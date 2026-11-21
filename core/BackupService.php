<?php
// core/BackupService.php
declare(strict_types=1);

/**
 * Servicio de Copias de Seguridad Multi-Tenant (.sql.gz)
 * - Super Admin: Respaldo Global completo o filtrado por un Entrenador específico.
 * - Entrenador: Respaldo aislado únicamente de sus propios atletas y recursos.
 * - Streaming GZIP con comillado seguro por tipo de columna (preserva VARCHAR numéricos).
 */
class BackupService
{
    private const NUMERIC_TYPES = [
        'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint',
        'decimal', 'numeric', 'float', 'double', 'real', 'bit'
    ];

    private const BINARY_TYPES = [
        'tinyblob', 'blob', 'mediumblob', 'longblob', 'binary', 'varbinary'
    ];

    /**
     * Descarga el respaldo GLOBAL completo (solo Super Admin).
     */
    public static function descargarRespaldo(PDO $db, ?string $filename = null): void {
        // Validar acceso a tablas ANTES de enviar headers GZIP
        $tables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
        if (!is_array($tables)) {
            throw new \RuntimeException('No se pudo consultar el esquema de base de datos.');
        }

        self::prepararEntornoStreaming();
        $filename = $filename ?: ('atlas_fit_global_' . date('Y-m-d_His') . '.sql.gz');
        self::enviarHeadersDescarga($filename);

        $gz = gzopen('php://output', 'wb6');
        if ($gz === false) {
            throw new \RuntimeException('No se pudo inicializar el flujo de compresión GZIP.');
        }

        self::ejecutarVolcadoGlobal($db, $gz);
        gzclose($gz);
        exit;
    }

    /**
     * Descarga el respaldo AISLADO de un entrenador y todos sus atletas (sin datos de terceros ni admin).
     */
    public static function descargarRespaldoEntrenador(PDO $db, int $entrenadorId, ?string $filename = null): void {
        if ($entrenadorId <= 0) {
            throw new \InvalidArgumentException('Entrenador inválido para respaldo.');
        }
        // Validar existencia del entrenador ANTES de emitir headers GZIP para poder devolver JSON limpio si falla
        $stChk = $db->prepare("SELECT id FROM usuarios WHERE id = ? AND rol = 'entrenador' LIMIT 1");
        $stChk->execute([$entrenadorId]);
        if (!$stChk->fetchColumn()) {
            throw new \RuntimeException('Entrenador no encontrado.');
        }

        self::prepararEntornoStreaming();
        $filename = $filename ?: ('atlas_fit_entrenador_' . $entrenadorId . '_' . date('Y-m-d_His') . '.sql.gz');
        self::enviarHeadersDescarga($filename);

        $gz = gzopen('php://output', 'wb6');
        if ($gz === false) {
            throw new \RuntimeException('No se pudo inicializar el flujo de compresión GZIP.');
        }

        self::ejecutarVolcadoEntrenador($db, $entrenadorId, $gz);
        gzclose($gz);
        exit;
    }

    /**
     * Genera un respaldo global en un archivo físico del servidor (uso exclusivo para Cron Jobs).
     */
    public static function guardarRespaldoEnArchivo(PDO $db, string $rutaDestino): array {
        @set_time_limit(0);
        @ini_set('memory_limit', '256M');

        $dir = dirname($rutaDestino);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0750, true) && !is_dir($dir)) {
                throw new \RuntimeException("No se pudo crear el directorio de respaldos: {$dir}");
            }
        }

        $gz = gzopen($rutaDestino, 'wb6');
        if ($gz === false) {
            throw new \RuntimeException("No se pudo abrir el archivo destino para escritura GZIP: {$rutaDestino}");
        }

        $stats = self::ejecutarVolcadoGlobal($db, $gz);
        gzclose($gz);

        $stats['ruta'] = $rutaDestino;
        $stats['bytes'] = @filesize($rutaDestino) ?: 0;
        return $stats;
    }

    /**
     * Mantiene solo los últimos N respaldos automáticos en la carpeta especificada.
     */
    public static function rotarRespaldosAntiguos(string $directorio, int $maxArchivos = 7): int {
        if (!is_dir($directorio)) return 0;

        $archivos = glob(rtrim($directorio, '/\\') . '/*.sql.gz');
        if (!$archivos || count($archivos) <= $maxArchivos) return 0;

        usort($archivos, static fn(string $a, string $b): int => (@filemtime($b) ?: 0) <=> (@filemtime($a) ?: 0));

        $purgados = 0;
        foreach (array_slice($archivos, $maxArchivos) as $archivoViejo) {
            if (is_file($archivoViejo) && @unlink($archivoViejo)) {
                $purgados++;
            }
        }
        return $purgados;
    }

    /**
     * Genera respaldo SQL filtrado de un entrenador en un stream GZIP (expuesto para pruebas y descarga).
     */
    public static function ejecutarVolcadoEntrenador(PDO $db, int $entrenadorId, $gz): array {
        $fechaGeneracion = date('Y-m-d H:i:s');

        $stEnt = $db->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ? AND rol = 'entrenador' LIMIT 1");
        $stEnt->execute([$entrenadorId]);
        $entRow = $stEnt->fetch(PDO::FETCH_ASSOC);
        if (!$entRow) {
            throw new \RuntimeException('Entrenador no encontrado.');
        }

        $entNombreClean = str_replace(["\r", "\n"], ' ', (string)$entRow['nombre']);
        $encabezado = <<<SQL
-- ======================================================================
-- ATLAS FIT — COPIA DE SEGURIDAD POR ENTRENADOR (.SQL)
-- Entrenador          : #{$entrenadorId} ({$entNombreClean})
-- Fecha de Generación : {$fechaGeneracion}
-- Alcance             : Exclusivo de los atletas y recursos del entrenador
-- Compatible          : MySQL / MariaDB / TiDB (sin DROP TABLE destructivo)
-- ======================================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;


SQL;
        gzwrite($gz, $encabezado);

        $existingTables = array_flip($db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN));

        // Obtener IDs de atletas y usuarios pertenecientes exclusivamente a este entrenador (excluyendo fila self-client del entrenador)
        $stCli = $db->prepare("SELECT id, usuario_id FROM clientes WHERE entrenador_id = ? AND usuario_id != ? ORDER BY id ASC");
        $stCli->execute([$entrenadorId, $entrenadorId]);
        $cliRows = $stCli->fetchAll(PDO::FETCH_ASSOC);
        $clienteIds = array_values(array_unique(array_filter(array_map(static fn($r) => (int)$r['id'], $cliRows))));
        $usuarioIds = array_values(array_unique(array_filter(array_map(static fn($r) => (int)$r['usuario_id'], $cliRows))));

        // Obtener IDs de rutinas y plantillas del entrenador o sus atletas
        $rutinaIds = [];
        if (!empty($clienteIds)) {
            $ph = implode(',', array_fill(0, count($clienteIds), '?'));
            $stR = $db->prepare("SELECT id FROM rutinas WHERE cliente_id IN ({$ph}) OR entrenador_id = ?");
            $stR->execute(array_merge($clienteIds, [$entrenadorId]));
            $rutinaIds = array_values(array_unique(array_map('intval', $stR->fetchAll(PDO::FETCH_COLUMN))));
        } else {
            $stR = $db->prepare("SELECT id FROM rutinas WHERE entrenador_id = ?");
            $stR->execute([$entrenadorId]);
            $rutinaIds = array_values(array_unique(array_map('intval', $stR->fetchAll(PDO::FETCH_COLUMN))));
        }

        $plantillaIds = [];
        if (isset($existingTables['plantillas_rutinas'])) {
            $stP = $db->prepare("SELECT id FROM plantillas_rutinas WHERE entrenador_id = ?");
            $stP->execute([$entrenadorId]);
            $plantillaIds = array_values(array_unique(array_map('intval', $stP->fetchAll(PDO::FETCH_COLUMN))));
        }

        $plantillaEjIds = [];
        if (!empty($plantillaIds) && isset($existingTables['plantilla_ejercicios'])) {
            $ph = implode(',', array_fill(0, count($plantillaIds), '?'));
            $stPE = $db->prepare("SELECT id FROM plantilla_ejercicios WHERE plantilla_id IN ({$ph})");
            $stPE->execute($plantillaIds);
            $plantillaEjIds = array_values(array_unique(array_map('intval', $stPE->fetchAll(PDO::FETCH_COLUMN))));
        }

        $seccionIds = [];
        if (isset($existingTables['tienda_secciones'])) {
            $stS = $db->prepare("SELECT id FROM tienda_secciones WHERE entrenador_id = ?");
            $stS->execute([$entrenadorId]);
            $seccionIds = array_values(array_unique(array_map('intval', $stS->fetchAll(PDO::FETCH_COLUMN))));
        }

        $totalTablas = 0;
        $totalFilas  = 0;

        // Definición estricta de consultas por tabla (solo filas propias del entrenador y sus atletas)
        $plan = [];
        if (!empty($usuarioIds)) {
            $phU = implode(',', array_fill(0, count($usuarioIds), '?'));
            $plan[] = ['usuarios', "SELECT id, nombre, email, rol, activo, estado_cuenta, creado_en FROM usuarios WHERE id IN ({$phU}) AND rol = 'cliente'", $usuarioIds];
        }
        $plan[] = ['clientes', 'SELECT * FROM clientes WHERE entrenador_id = ? AND usuario_id != ?', [$entrenadorId, $entrenadorId]];
        if (!empty($clienteIds)) {
            $phC = implode(',', array_fill(0, count($clienteIds), '?'));
            $plan[] = ['cliente_objetivos', "SELECT * FROM cliente_objetivos WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['cliente_medidas', "SELECT * FROM cliente_medidas WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['peso_historial', "SELECT * FROM peso_historial WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['metas', "SELECT * FROM metas WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['metas_progreso', "SELECT mp.* FROM metas_progreso mp JOIN metas m ON m.id = mp.meta_id WHERE m.cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['pagos', "SELECT * FROM pagos WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['sesiones', "SELECT * FROM sesiones WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['sesion_series', "SELECT * FROM sesion_series WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['sesiones_asistencia', "SELECT * FROM sesiones_asistencia WHERE cliente_id IN ({$phC})", $clienteIds];
            $plan[] = ['rutinas_aplazamientos', "SELECT * FROM rutinas_aplazamientos WHERE cliente_id IN ({$phC})", $clienteIds];
        }
        if (!empty($rutinaIds)) {
            $phR = implode(',', array_fill(0, count($rutinaIds), '?'));
            $plan[] = ['rutinas', "SELECT * FROM rutinas WHERE id IN ({$phR})", $rutinaIds];
            $plan[] = ['rutina_dias', "SELECT * FROM rutina_dias WHERE rutina_id IN ({$phR})", $rutinaIds];
            $plan[] = ['ejercicios', "SELECT * FROM ejercicios WHERE rutina_id IN ({$phR})", $rutinaIds];
        }
        if (!empty($plantillaIds)) {
            $phP = implode(',', array_fill(0, count($plantillaIds), '?'));
            $plan[] = ['plantillas_rutinas', "SELECT * FROM plantillas_rutinas WHERE id IN ({$phP})", $plantillaIds];
            $plan[] = ['plantilla_ejercicios', "SELECT * FROM plantilla_ejercicios WHERE plantilla_id IN ({$phP})", $plantillaIds];
        }
        if (!empty($plantillaEjIds)) {
            $phPE = implode(',', array_fill(0, count($plantillaEjIds), '?'));
            $plan[] = ['plantilla_series', "SELECT * FROM plantilla_series WHERE plantilla_ej_id IN ({$phPE})", $plantillaEjIds];
        }
        $plan[] = ['metodos_pago', 'SELECT * FROM metodos_pago WHERE entrenador_id = ?', [$entrenadorId]];
        $plan[] = ['catalogo_ejercicios', 'SELECT * FROM catalogo_ejercicios WHERE entrenador_id = ?', [$entrenadorId]];
        $plan[] = ['tienda_secciones', 'SELECT * FROM tienda_secciones WHERE entrenador_id = ?', [$entrenadorId]];
        if (!empty($seccionIds)) {
            $phS = implode(',', array_fill(0, count($seccionIds), '?'));
            $plan[] = ['tienda_productos', "SELECT * FROM tienda_productos WHERE seccion_id IN ({$phS})", $seccionIds];
        }

        foreach ($plan as [$table, $sql, $params]) {
            if (!isset($existingTables[$table])) continue;
            [$numericCols, $binaryCols] = self::obtenerTiposColumnas($db, $table);
            $st = $db->prepare($sql);
            $st->execute($params);
            $filasTabla = self::volcarStatementConColumnas($db, $st, $table, $numericCols, $binaryCols, $gz, true);
            $totalTablas++;
            $totalFilas += $filasTabla;
        }

        $totalAtletas = count($clienteIds);
        $cierre = <<<SQL
-- ======================================================================
-- FINALIZACIÓN DE COPIA DE SEGURIDAD POR ENTRENADOR
-- Entrenador ID       : {$entrenadorId} ({$totalAtletas} atletas)
-- Tablas Exportadas   : {$totalTablas}
-- Registros Totales   : {$totalFilas}
-- ======================================================================

SET FOREIGN_KEY_CHECKS=1;

SQL;
        gzwrite($gz, $cierre);

        return ['tablas' => $totalTablas, 'filas' => $totalFilas, 'atletas' => $totalAtletas];
    }

    /**
     * Volcado GLOBAL completo (esquema DDL + datos DML con comillado seguro por tipo de columna).
     */
    private static function ejecutarVolcadoGlobal(PDO $db, $gz): array {
        $fechaGeneracion = date('Y-m-d H:i:s');
        $encabezado = <<<SQL
-- ======================================================================
-- ATLAS FIT — COPIA DE SEGURIDAD GLOBAL DE BASE DE DATOS (.SQL)
-- Fecha de Generación : {$fechaGeneracion}
-- Sistema             : Atlas Fit Management Platform
-- Servidor            : MySQL / MariaDB / TiDB Compatible
-- ======================================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8mb4;
SET time_zone = "+00:00";


SQL;
        gzwrite($gz, $encabezado);

        $tables = $db->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'")->fetchAll(PDO::FETCH_COLUMN);
        $totalTablas = count($tables);
        $totalFilasGlobales = 0;

        foreach ($tables as $table) {
            $stCreate = $db->query("SHOW CREATE TABLE `{$table}`");
            $rowCreate = $stCreate->fetch(PDO::FETCH_ASSOC);
            $createTableSql = $rowCreate['Create Table'] ?? '';

            $seccionTabla = <<<SQL
-- ----------------------------------------------------------------------
-- Estructura de tabla para: `{$table}`
-- ----------------------------------------------------------------------
DROP TABLE IF EXISTS `{$table}`;
{$createTableSql};


SQL;
            gzwrite($gz, $seccionTabla);

            [$numericCols, $binaryCols] = self::obtenerTiposColumnas($db, (string)$table);
            $stData = $db->query("SELECT * FROM `{$table}`");
            $totalFilasGlobales += self::volcarStatementConColumnas($db, $stData, (string)$table, $numericCols, $binaryCols, $gz, false);
        }

        $cierre = <<<SQL
-- ======================================================================
-- FINALIZACIÓN DE COPIA DE SEGURIDAD GLOBAL
-- Tablas Respaldadas : {$totalTablas}
-- Registros Totales  : {$totalFilasGlobales}
-- ======================================================================

SET FOREIGN_KEY_CHECKS=1;

SQL;
        gzwrite($gz, $cierre);

        return ['tablas' => $totalTablas, 'filas' => $totalFilasGlobales];
    }

    private static function obtenerTiposColumnas(PDO $db, string $table): array {
        $numMap = [];
        $binMap = [];
        try {
            $cols = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($cols as $c) {
                $baseType = strtolower(preg_replace('/[^a-z].*$/i', '', (string)($c['Type'] ?? '')));
                $field = (string)$c['Field'];
                if (in_array($baseType, self::NUMERIC_TYPES, true)) {
                    $numMap[$field] = true;
                } elseif (in_array($baseType, self::BINARY_TYPES, true)) {
                    $binMap[$field] = true;
                }
            }
        } catch (\Throwable $e) {}
        return [$numMap, $binMap];
    }

    private static function volcarStatementConColumnas(
        PDO $db,
        PDOStatement $st,
        string $table,
        array $numericCols,
        array $binaryCols,
        $gz,
        bool $useInsertIgnore
    ): int {
        $count = 0;
        $chunk = [];
        // Tablas con BLOBs (ej. archivos_media) se vuelcan 1 fila por INSERT para evitar picos de RAM y max_allowed_packet
        $chunkSize = !empty($binaryCols) ? 1 : 150;
        $colHeaderSql = '';
        $prefix = $useInsertIgnore ? 'INSERT IGNORE INTO' : 'INSERT INTO';

        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            if ($colHeaderSql === '') {
                $colNames = array_map(static fn(string $c): string => '`' . str_replace('`', '``', $c) . '`', array_keys($row));
                $colHeaderSql = ' (' . implode(', ', $colNames) . ')';
                gzwrite($gz, "-- Volcando datos para la tabla: `{$table}`\n");
            }

            $valores = [];
            foreach ($row as $colName => $colVal) {
                if ($colVal === null) {
                    $valores[] = 'NULL';
                } elseif (isset($binaryCols[$colName])) {
                    $binStr = (string)$colVal;
                    $valores[] = ($binStr === '') ? "''" : ('0x' . bin2hex($binStr));
                } elseif (isset($numericCols[$colName]) && is_numeric($colVal)) {
                    $valores[] = (string)$colVal;
                } else {
                    $valores[] = $db->quote((string)$colVal);
                }
            }

            $chunk[] = '(' . implode(', ', $valores) . ')';
            $count++;

            if (count($chunk) >= $chunkSize) {
                gzwrite($gz, "{$prefix} `{$table}`{$colHeaderSql} VALUES\n" . implode(",\n", $chunk) . ";\n");
                $chunk = [];
            }
        }

        if (!empty($chunk)) {
            gzwrite($gz, "{$prefix} `{$table}`{$colHeaderSql} VALUES\n" . implode(",\n", $chunk) . ";\n");
        }
        if ($count > 0) {
            gzwrite($gz, "\n");
        }
        return $count;
    }

    private static function prepararEntornoStreaming(): void {
        @set_time_limit(0);
        @ini_set('memory_limit', '256M');
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    private static function enviarHeadersDescarga(string $filename): void {
        header('Content-Type: application/gzip');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
