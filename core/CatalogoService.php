<?php
/**
 * core/CatalogoService.php
 * Single Source of Truth (SSOT) para el Catálogo de Ejercicios con alcance dual:
 * - Super Admin (entrenadorId === null): gestiona el catálogo global (es_publico = 1, entrenador_id IS NULL).
 * - Entrenador  (entrenadorId > 0):      ve el catálogo global + sus propios ejercicios/ediciones privados
 *   (entrenador_id = $entId, es_publico = 0), pudiendo editar u ocultar ejercicios globales solo para sí y sus atletas.
 */

class CatalogoService {

    /**
     * Garantiza de forma idempotente (una vez por request) que catalogo_ejercicios
     * posea las columnas `origen_global_id` y `oculto`.
     */
    public static function asegurarColumnasScope(PDO $db): void {
        static $verificado = false;
        if ($verificado) {
            return;
        }
        $verificado = true;
        try {
            $cols = array_column(
                $db->query("SHOW COLUMNS FROM catalogo_ejercicios")->fetchAll(PDO::FETCH_ASSOC),
                'Field'
            );
            if (!in_array('origen_global_id', $cols, true)) {
                $db->exec("ALTER TABLE catalogo_ejercicios ADD COLUMN origen_global_id INT UNSIGNED DEFAULT NULL AFTER entrenador_id");
            }
            if (!in_array('oculto', $cols, true)) {
                $db->exec("ALTER TABLE catalogo_ejercicios ADD COLUMN oculto TINYINT(1) NOT NULL DEFAULT 0 AFTER origen_global_id");
            }
        } catch (\Throwable $e) {
            error_log('[CatalogoService::asegurarColumnasScope] ' . $e->getMessage());
        }
    }

    /**
     * Normaliza el grupo muscular al ID canónico en categorias_musculares.
     */
    public static function resolverCategoriaId(PDO $db, ?string $grupo): ?int {
        if (!$grupo) return null;
        $g = mb_strtolower(trim($grupo), 'UTF-8');
        $norm = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'u', 'n'], $g);

        $canon = 'Otro';
        if (strpos($norm, 'pech') !== false || strpos($norm, 'pector') !== false) $canon = 'Pecho';
        elseif (strpos($norm, 'espalda') !== false || strpos($norm, 'dorsal') !== false) $canon = 'Espalda';
        elseif (strpos($norm, 'hombr') !== false || strpos($norm, 'deltoid') !== false) $canon = 'Hombros';
        elseif (strpos($norm, 'bicep') !== false) $canon = 'Bíceps';
        elseif (strpos($norm, 'tricep') !== false) $canon = 'Tríceps';
        elseif (strpos($norm, 'trapeci') !== false) $canon = 'Trapecio';
        elseif (strpos($norm, 'antebraz') !== false) $canon = 'Antebrazos';
        elseif (strpos($norm, 'cuello') !== false) $canon = 'Cuello';
        elseif (strpos($norm, 'cuadicep') !== false || strpos($norm, 'cuadricep') !== false) $canon = 'Cuádriceps';
        elseif (strpos($norm, 'femor') !== false || strpos($norm, 'isquio') !== false) $canon = 'Femoral';
        elseif (strpos($norm, 'glute') !== false) $canon = 'Glúteos';
        elseif (strpos($norm, 'gemel') !== false || strpos($norm, 'pantorrill') !== false) $canon = 'Gemelos';
        elseif (strpos($norm, 'abduct') !== false) $canon = 'Abductores';
        elseif (strpos($norm, 'aduct') !== false) $canon = 'Aductores';
        elseif (strpos($norm, 'abdom') !== false || strpos($norm, 'core') !== false) $canon = 'Abdomen';
        elseif (strpos($norm, 'cardio') !== false || strpos($norm, 'aerob') !== false) $canon = 'Cardio';
        elseif (strpos($norm, 'full') !== false || strpos($norm, 'cuerpo') !== false || strpos($norm, 'entero') !== false) $canon = 'Full Body';

        $st = $db->prepare("SELECT id FROM categorias_musculares WHERE nombre = ? LIMIT 1");
        $st->execute([$canon]);
        $id = $st->fetchColumn();
        return $id ? (int)$id : null;
    }

    /**
     * Sube y almacena imagen/video para portada, demo o pasos de configuración.
     */
    public static function procesarMedia(array $fileInfo, string $prefijo = 'ej_'): ?string {
        if (!isset($fileInfo['error']) || $fileInfo['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        $ext = strtolower(pathinfo((string)$fileInfo['name'], PATHINFO_EXTENSION));
        $validExts = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'mov', 'webm'];
        if (!in_array($ext, $validExts, true)) {
            return null;
        }
        $saveExt = in_array($ext, ['jpg', 'jpeg', 'png'], true) ? 'jpg' : $ext;
        $relMedia = 'public/uploads/ejercicios/' . uniqid($prefijo) . '.' . $saveExt;
        $rawBytes = (string)@file_get_contents($fileInfo['tmp_name']);
        if ($rawBytes !== '' && guardarMediaPermanente($rawBytes, $relMedia, 800, 80, false)) {
            return $relMedia;
        }
        return null;
    }

    /**
     * Elimina un archivo de media solo si ninguna otra fila del catálogo lo sigue referenciando
     * (evita que un entrenador al editar/borrar un override elimine la imagen global compartida).
     */
    public static function eliminarMediaSiHuerfana(PDO $db, ?string $relPath, int $excludeId = 0): void {
        $relPath = trim((string)$relPath);
        if ($relPath === '') {
            return;
        }
        try {
            $likePath = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $relPath) . '%';
            $st = $db->prepare(
                "SELECT COUNT(*) FROM catalogo_ejercicios
                 WHERE id != ?
                   AND (imagen_path = ? OR imagen_demo_path = ? OR configuracion_maquina LIKE ?)"
            );
            $st->execute([$excludeId, $relPath, $relPath, $likePath]);
            if ((int)$st->fetchColumn() === 0) {
                eliminarMediaPermanente($relPath);
            }
        } catch (\Throwable $e) {
            error_log('[CatalogoService::eliminarMediaSiHuerfana] ' . $e->getMessage());
        }
    }

    /**
     * Limpia archivos en pasos de configuración de máquina que fueron quitados.
     */
    public static function procesarPasosConfiguracion(PDO $db, ?string $rawPasosJson, int $excludeId = 0): ?string {
        if (!$rawPasosJson) return null;
        $decoded = json_decode($rawPasosJson, true);
        if (!is_array($decoded)) return null;

        $limpios = [];
        foreach ($decoded as $idx => $step) {
            $texto = trim((string)($step['texto'] ?? ''));
            $media = isset($step['media']) ? trim((string)$step['media']) : null;
            if (!empty($step['eliminar_media'])) {
                if ($media) self::eliminarMediaSiHuerfana($db, $media, $excludeId);
                $media = null;
            }

            $fileKey = 'cfg_step_file_' . $idx;
            if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
                $nuevoMedia = self::procesarMedia($_FILES[$fileKey], 'cfg_p' . ($idx + 1) . '_');
                if ($nuevoMedia) {
                    if ($media) self::eliminarMediaSiHuerfana($db, $media, $excludeId);
                    $media = $nuevoMedia;
                }
            }

            if ($texto !== '' || $media !== null) {
                $tipoMedia = null;
                if ($media) {
                    $ext = strtolower(pathinfo($media, PATHINFO_EXTENSION));
                    $tipoMedia = in_array($ext, ['mp4', 'mov', 'webm'], true) ? 'video' : 'image';
                }
                $limpios[] = [
                    'paso'       => count($limpios) + 1,
                    'texto'      => $texto,
                    'media'      => $media,
                    'tipo_media' => $tipoMedia,
                ];
            }
        }
        return !empty($limpios) ? json_encode($limpios, JSON_UNESCAPED_UNICODE) : null;
    }

    /**
     * Lista ejercicios visibles según el alcance:
     * - $entId === null -> Solo catálogo global (Super Admin).
     * - $entId > 0      -> Privados/overrides del entrenador + globales no reemplazados ni ocultos.
     */
    public static function obtenerEjerciciosVisibles(PDO $db, ?int $entId = null): array {
        self::asegurarColumnasScope($db);

        $cols = "c.id, c.nombre, c.grupo_muscular, c.categoria_id, c.descripcion, c.imagen_path,
                 c.imagen_demo_path, c.video_path, c.video_url, c.indicaciones, c.configuracion_maquina,
                 c.tipo_implemento, c.unidad_carga, c.equipamiento, c.nota_default,
                 c.entrenador_id, c.origen_global_id, c.es_publico, cm.nombre AS categoria_nombre";

        if ($entId === null || $entId <= 0) {
            $st = $db->query(
                "SELECT {$cols}, 0 AS es_propio
                 FROM catalogo_ejercicios c
                 LEFT JOIN categorias_musculares cm ON cm.id = c.categoria_id
                 WHERE c.entrenador_id IS NULL AND COALESCE(c.oculto, 0) = 0
                 ORDER BY c.grupo_muscular ASC, c.nombre ASC"
            );
            return $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];
        }

        $st = $db->prepare(
            "SELECT {$cols},
                    CASE WHEN c.entrenador_id = ? THEN 1 ELSE 0 END AS es_propio
             FROM catalogo_ejercicios c
             LEFT JOIN categorias_musculares cm ON cm.id = c.categoria_id
             WHERE COALESCE(c.oculto, 0) = 0
               AND (
                   c.entrenador_id = ?
                   OR (
                       c.entrenador_id IS NULL
                       AND NOT EXISTS (
                           SELECT 1 FROM catalogo_ejercicios ov
                           WHERE ov.entrenador_id = ? AND ov.origen_global_id = c.id
                       )
                   )
               )
             ORDER BY c.grupo_muscular ASC, c.nombre ASC"
        );
        $st->execute([$entId, $entId, $entId]);
        return $st->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Agrupa los ejercicios visibles por nombre de grupo para la vista de Configuración.
     */
    public static function obtenerCatalogoAgrupado(PDO $db, ?int $entId = null): array {
        $rows = self::obtenerEjerciciosVisibles($db, $entId);
        $porGrupo = [];
        foreach ($rows as $ej) {
            $grupo = $ej['categoria_nombre'] ?: ($ej['grupo_muscular'] ?: 'Sin grupo');
            $porGrupo[$grupo][] = $ej;
        }
        ksort($porGrupo);
        return $porGrupo;
    }

    /**
     * Devuelve un mapa asociativo ['nombre_normalizado' => fila] con resolución determinista
     * (prioriza ediciones privadas del entrenador sobre el catálogo global), evitando JOINs N:1.
     */
    public static function obtenerMapaPorNombre(PDO $db, ?int $entId = null): array {
        $rows = self::obtenerEjerciciosVisibles($db, $entId);
        $mapa = [];
        foreach ($rows as $r) {
            $k = mb_strtolower(trim((string)($r['nombre'] ?? '')), 'UTF-8');
            if ($k === '') continue;
            if (!isset($mapa[$k]) || !empty($r['es_propio'])) {
                $mapa[$k] = $r;
            }
        }
        return $mapa;
    }

    /**
     * Busca determinísticamente un ejercicio por nombre respetando el alcance del entrenador (override primero, luego global).
     */
    public static function obtenerMetaPorNombre(PDO $db, string $nombre, ?int $entId = null): ?array {
        self::asegurarColumnasScope($db);
        $nombreClean = trim($nombre);
        if ($nombreClean === '') {
            return null;
        }
        if ($entId !== null && $entId > 0) {
            $st = $db->prepare(
                "SELECT c.*
                 FROM catalogo_ejercicios c
                 WHERE LOWER(TRIM(c.nombre)) = LOWER(TRIM(?))
                   AND COALESCE(c.oculto, 0) = 0
                   AND (c.entrenador_id = ? OR c.entrenador_id IS NULL)
                   AND NOT (
                       c.entrenador_id IS NULL
                       AND EXISTS (
                           SELECT 1 FROM catalogo_ejercicios ov
                           WHERE ov.entrenador_id = ? AND ov.origen_global_id = c.id
                       )
                   )
                 ORDER BY (c.entrenador_id = ?) DESC, c.id DESC
                 LIMIT 1"
            );
            $st->execute([$nombreClean, $entId, $entId, $entId]);
        } else {
            $st = $db->prepare(
                "SELECT c.*
                 FROM catalogo_ejercicios c
                 WHERE LOWER(TRIM(c.nombre)) = LOWER(TRIM(?))
                   AND COALESCE(c.oculto, 0) = 0
                   AND c.entrenador_id IS NULL
                 ORDER BY c.id DESC
                 LIMIT 1"
            );
            $st->execute([$nombreClean]);
        }
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * Endpoint JSON: listar ejercicios visibles para Super Admin ($entId = null) o Entrenador ($entId > 0).
     */
    public static function apiList(PDO $db, ?int $entId = null): void {
        $rows = self::obtenerEjerciciosVisibles($db, $entId);
        usort($rows, static fn(array $a, array $b): int => strcasecmp((string)$a['nombre'], (string)$b['nombre']));
        jsonOut(true, 'ok', $rows);
    }

    /**
     * Endpoint JSON: agregar ejercicio global (Super Admin) o privado (Entrenador).
     */
    public static function apiAgregar(PDO $db, ?int $ownerEntId = null): void {
        self::asegurarColumnasScope($db);

        $nombre       = trim((string)($_POST['nombre'] ?? ''));
        $grupo        = trim((string)($_POST['grupo_muscular'] ?? 'Otro')) ?: 'Otro';
        $desc         = trim((string)($_POST['descripcion'] ?? ''));
        $indicaciones = trim((string)($_POST['indicaciones'] ?? $desc));
        $videoUrl     = trim((string)($_POST['video_url'] ?? ''));
        $tipoImp      = in_array($_POST['tipo_implemento'] ?? '', ['mancuerna', 'barra', 'polea_maquina', 'sin_peso'], true)
                        ? $_POST['tipo_implemento'] : 'barra';
        $unidadCarga  = in_array($_POST['unidad_carga'] ?? '', ['kg', 'lbs', 'placas', 'ninguna'], true)
                        ? $_POST['unidad_carga'] : 'kg';

        if ($nombre === '') {
            jsonOut(false, 'El nombre del ejercicio es obligatorio.');
        }

        try {
            $mediaPortadaPath = null;
            if (isset($_FILES['media_portada']) && $_FILES['media_portada']['error'] === UPLOAD_ERR_OK) {
                $mediaPortadaPath = self::procesarMedia($_FILES['media_portada'], 'portada_');
            } elseif (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $mediaPortadaPath = self::procesarMedia($_FILES['media'], 'portada_');
            }

            $mediaDemoPath = null;
            if (isset($_FILES['media_demo']) && $_FILES['media_demo']['error'] === UPLOAD_ERR_OK) {
                $mediaDemoPath = self::procesarMedia($_FILES['media_demo'], 'demo_');
            }

            $cfgMaquinaJson = self::procesarPasosConfiguracion($db, $_POST['configuracion_maquina'] ?? null, 0);
            $catId          = self::resolverCategoriaId($db, $grupo);

            $esPublico    = ($ownerEntId === null) ? 1 : 0;
            $entrenadorId = ($ownerEntId !== null && $ownerEntId > 0) ? $ownerEntId : null;

            $stmt = $db->prepare(
                "INSERT INTO catalogo_ejercicios
                 (nombre, grupo_muscular, categoria_id, descripcion, indicaciones, configuracion_maquina,
                  imagen_path, imagen_demo_path, video_url, tipo_implemento, unidad_carga, nota_default,
                  es_publico, entrenador_id, origen_global_id, oculto)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '', ?, ?, NULL, 0)"
            );
            $ok = $stmt->execute([
                $nombre, $grupo, $catId, $desc, $indicaciones, $cfgMaquinaJson,
                $mediaPortadaPath, $mediaDemoPath, $videoUrl, $tipoImp, $unidadCarga,
                $esPublico, $entrenadorId
            ]);

            if ($ok) {
                $msg = ($ownerEntId === null)
                    ? 'Ejercicio global agregado al catálogo para todos los entrenadores.'
                    : 'Ejercicio agregado a tu catálogo privado correctamente.';
                jsonOut(true, $msg, ['id' => (int)$db->lastInsertId()]);
            }
            jsonOut(false, 'Error al guardar el ejercicio.');
        } catch (\Throwable $e) {
            error_log('[CatalogoService::apiAgregar] ' . $e->getMessage());
            jsonOut(false, 'No se pudo agregar el ejercicio.');
        }
    }

    /**
     * Endpoint JSON: editar ejercicio.
     * - Super Admin: edita el registro global directamente.
     * - Entrenador: si edita uno propio lo actualiza; si edita uno global crea/actualiza su override privado.
     */
    public static function apiEditar(PDO $db, ?int $ownerEntId = null): void {
        self::asegurarColumnasScope($db);

        $id           = (int)($_POST['id'] ?? 0);
        $nombre       = trim((string)($_POST['nombre'] ?? ''));
        $grupo        = trim((string)($_POST['grupo_muscular'] ?? 'Otro')) ?: 'Otro';
        $desc         = trim((string)($_POST['descripcion'] ?? ''));
        $indicaciones = trim((string)($_POST['indicaciones'] ?? $desc));
        $videoUrl     = trim((string)($_POST['video_url'] ?? ''));
        $tipoImp      = in_array($_POST['tipo_implemento'] ?? '', ['mancuerna', 'barra', 'polea_maquina', 'sin_peso'], true)
                        ? $_POST['tipo_implemento'] : 'barra';
        $unidadCarga  = in_array($_POST['unidad_carga'] ?? '', ['kg', 'lbs', 'placas', 'ninguna'], true)
                        ? $_POST['unidad_carga'] : 'kg';

        if (!$id || $nombre === '') {
            jsonOut(false, 'Faltan datos obligatorios.');
        }

        try {
            $chk = $db->prepare("SELECT * FROM catalogo_ejercicios WHERE id = ? LIMIT 1");
            $chk->execute([$id]);
            $ej = $chk->fetch(PDO::FETCH_ASSOC);
            if (!$ej) {
                jsonOut(false, 'El ejercicio no existe.');
            }

            $ejEnt = ($ej['entrenador_id'] !== null && (int)$ej['entrenador_id'] > 0) ? (int)$ej['entrenador_id'] : null;

            if ($ownerEntId === null) {
                if ($ejEnt !== null) {
                    jsonOut(false, 'Solo puedes editar ejercicios globales desde este panel.', [], 403);
                }
            } else {
                if ($ejEnt !== null && $ejEnt !== $ownerEntId) {
                    jsonOut(false, 'No tienes permisos para editar el ejercicio privado de otro entrenador.', [], 403);
                }
            }

            // Si un entrenador edita un ejercicio global, verificar si ya existe un override previo o crear uno nuevo
            $esForkGlobal = ($ownerEntId !== null && $ejEnt === null);
            $targetRow    = $ej;
            $targetId     = $id;

            if ($esForkGlobal) {
                $stOv = $db->prepare(
                    "SELECT * FROM catalogo_ejercicios WHERE entrenador_id = ? AND origen_global_id = ? LIMIT 1"
                );
                $stOv->execute([$ownerEntId, $id]);
                $existingOv = $stOv->fetch(PDO::FETCH_ASSOC);
                if ($existingOv) {
                    $targetRow = $existingOv;
                    $targetId  = (int)$existingOv['id'];
                    $esForkGlobal = false;
                }
            }

            $nombreAnterior = trim((string)($targetRow['nombre'] ?? ''));
            $mediaPortadaPath = $targetRow['imagen_path'] ?? null;
            $mediaDemoPath    = $targetRow['imagen_demo_path'] ?? null;
            $excludeCheckId   = $esForkGlobal ? 0 : $targetId;

            if (!empty($_POST['eliminar_portada'])) {
                if ($mediaPortadaPath && !$esForkGlobal) {
                    self::eliminarMediaSiHuerfana($db, (string)$mediaPortadaPath, $excludeCheckId);
                }
                $mediaPortadaPath = null;
            }
            if (isset($_FILES['media_portada']) && $_FILES['media_portada']['error'] === UPLOAD_ERR_OK) {
                $nuevaPortada = self::procesarMedia($_FILES['media_portada'], 'portada_');
                if ($nuevaPortada) {
                    if ($mediaPortadaPath && !$esForkGlobal) {
                        self::eliminarMediaSiHuerfana($db, (string)$mediaPortadaPath, $excludeCheckId);
                    }
                    $mediaPortadaPath = $nuevaPortada;
                }
            } elseif (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                $nuevaPortada = self::procesarMedia($_FILES['media'], 'portada_');
                if ($nuevaPortada) {
                    if ($mediaPortadaPath && !$esForkGlobal) {
                        self::eliminarMediaSiHuerfana($db, (string)$mediaPortadaPath, $excludeCheckId);
                    }
                    $mediaPortadaPath = $nuevaPortada;
                }
            }

            if (!empty($_POST['eliminar_demo'])) {
                if ($mediaDemoPath && !$esForkGlobal) {
                    self::eliminarMediaSiHuerfana($db, (string)$mediaDemoPath, $excludeCheckId);
                }
                $mediaDemoPath = null;
            }
            if (isset($_FILES['media_demo']) && $_FILES['media_demo']['error'] === UPLOAD_ERR_OK) {
                $nuevaDemo = self::procesarMedia($_FILES['media_demo'], 'demo_');
                if ($nuevaDemo) {
                    if ($mediaDemoPath && !$esForkGlobal) {
                        self::eliminarMediaSiHuerfana($db, (string)$mediaDemoPath, $excludeCheckId);
                    }
                    $mediaDemoPath = $nuevaDemo;
                }
            }

            $cfgMaquinaJson = self::procesarPasosConfiguracion(
                $db,
                $_POST['configuracion_maquina'] ?? null,
                $excludeCheckId
            );
            $catId = self::resolverCategoriaId($db, $grupo);

            $db->beginTransaction();

            if ($esForkGlobal) {
                $stIns = $db->prepare(
                    "INSERT INTO catalogo_ejercicios
                     (nombre, grupo_muscular, categoria_id, equipamiento, descripcion, indicaciones,
                      configuracion_maquina, imagen_path, imagen_demo_path, video_path, video_url,
                      tipo_implemento, unidad_carga, nota_default, es_publico, entrenador_id, origen_global_id, oculto)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?, ?, 0)"
                );
                $stIns->execute([
                    $nombre, $grupo, $catId, $ej['equipamiento'] ?? null, $desc, $indicaciones,
                    $cfgMaquinaJson, $mediaPortadaPath, $mediaDemoPath, $ej['video_path'] ?? null, $videoUrl,
                    $tipoImp, $unidadCarga, (string)($ej['nota_default'] ?? ''),
                    $ownerEntId, $id
                ]);
                $targetId = (int)$db->lastInsertId();
            } else {
                $stUp = $db->prepare(
                    "UPDATE catalogo_ejercicios
                     SET nombre = ?, grupo_muscular = ?, categoria_id = ?, descripcion = ?, indicaciones = ?,
                         configuracion_maquina = ?, imagen_path = ?, imagen_demo_path = ?, video_url = ?,
                         tipo_implemento = ?, unidad_carga = ?, oculto = 0
                     WHERE id = ?"
                );
                $stUp->execute([
                    $nombre, $grupo, $catId, $desc, $indicaciones,
                    $cfgMaquinaJson, $mediaPortadaPath, $mediaDemoPath, $videoUrl,
                    $tipoImp, $unidadCarga, $targetId
                ]);
            }

            // Propagación estrictamente acotada al alcance del autor del cambio
            if ($nombreAnterior !== '') {
                self::propagarCambioARutinasYPlantillas(
                    $db,
                    $ownerEntId,
                    $esForkGlobal ? $id : ($targetRow['origen_global_id'] ? (int)$targetRow['origen_global_id'] : $targetId),
                    $nombreAnterior,
                    $nombre,
                    $grupo,
                    $tipoImp,
                    $unidadCarga,
                    $indicaciones
                );
            }

            $db->commit();

            $msg = ($ownerEntId === null)
                ? 'Ejercicio global actualizado y sincronizado.'
                : 'Ejercicio actualizado solo para ti y tus atletas.';
            jsonOut(true, $msg, ['id' => $targetId]);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[CatalogoService::apiEditar] ' . $e->getMessage());
            jsonOut(false, 'Error al actualizar el ejercicio.');
        }
    }

    /**
     * Endpoint JSON: eliminar ejercicio respetando alcance global vs privado por entrenador.
     */
    public static function apiBorrar(PDO $db, ?int $ownerEntId = null): void {
        self::asegurarColumnasScope($db);

        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            jsonOut(false, 'ID no válido.');
        }

        try {
            $st = $db->prepare("SELECT * FROM catalogo_ejercicios WHERE id = ? LIMIT 1");
            $st->execute([$id]);
            $ej = $st->fetch(PDO::FETCH_ASSOC);
            if (!$ej) {
                jsonOut(false, 'El ejercicio no existe.');
            }

            $ejEnt = ($ej['entrenador_id'] !== null && (int)$ej['entrenador_id'] > 0) ? (int)$ej['entrenador_id'] : null;

            if ($ownerEntId === null) {
                if ($ejEnt !== null) {
                    jsonOut(false, 'No puedes eliminar un ejercicio privado de un entrenador desde aquí.', [], 403);
                }

                $db->beginTransaction();
                // Limpiar marcadores ocultos que apuntaban a este global y preservar como propios los overrides personalizados
                $db->prepare("DELETE FROM catalogo_ejercicios WHERE origen_global_id = ? AND oculto = 1")->execute([$id]);
                $db->prepare("UPDATE catalogo_ejercicios SET origen_global_id = NULL WHERE origen_global_id = ?")->execute([$id]);
                $db->prepare("DELETE FROM catalogo_ejercicios WHERE id = ? AND entrenador_id IS NULL")->execute([$id]);
                $db->commit();

                self::eliminarMediaSiHuerfana($db, $ej['imagen_path'] ?? null, 0);
                self::eliminarMediaSiHuerfana($db, $ej['imagen_demo_path'] ?? null, 0);
                jsonOut(true, 'Ejercicio eliminado del catálogo global.');
            }

            // Rol Entrenador: nunca borra una fila global; la oculta solo para sí mismo
            if ($ejEnt !== null && $ejEnt !== $ownerEntId) {
                jsonOut(false, 'No tienes permiso para eliminar este ejercicio.', [], 403);
            }

            if ($ejEnt === null) {
                // Ocultar global para este entrenador (upsert sobre origen_global_id)
                $stOv = $db->prepare(
                    "SELECT id FROM catalogo_ejercicios WHERE entrenador_id = ? AND origen_global_id = ? LIMIT 1"
                );
                $stOv->execute([$ownerEntId, $id]);
                $ovId = (int)$stOv->fetchColumn();
                if ($ovId > 0) {
                    $db->prepare("UPDATE catalogo_ejercicios SET oculto = 1 WHERE id = ? AND entrenador_id = ?")
                       ->execute([$ovId, $ownerEntId]);
                } else {
                    $db->prepare(
                        "INSERT INTO catalogo_ejercicios
                         (nombre, grupo_muscular, categoria_id, tipo_implemento, unidad_carga, nota_default,
                          es_publico, entrenador_id, origen_global_id, oculto)
                         VALUES (?, ?, ?, ?, ?, '', 0, ?, ?, 1)"
                    )->execute([
                        $ej['nombre'],
                        $ej['grupo_muscular'] ?? 'Otro',
                        $ej['categoria_id'] ?? null,
                        $ej['tipo_implemento'] ?? 'barra',
                        $ej['unidad_carga'] ?? 'kg',
                        $ownerEntId,
                        $id
                    ]);
                }
                jsonOut(true, 'Ejercicio ocultado de tu catálogo personal.');
            }

            if (!empty($ej['origen_global_id'])) {
                // Tenía override de un global: marcar como oculto para que tampoco reaparezca el global al entrenador
                $db->prepare("UPDATE catalogo_ejercicios SET oculto = 1 WHERE id = ? AND entrenador_id = ?")
                   ->execute([$id, $ownerEntId]);
                jsonOut(true, 'Ejercicio eliminado de tu catálogo personal.');
            }

            // Ejercicio 100% creado por el entrenador: borrado físico seguro
            $db->prepare("DELETE FROM catalogo_ejercicios WHERE id = ? AND entrenador_id = ?")->execute([$id, $ownerEntId]);
            self::eliminarMediaSiHuerfana($db, $ej['imagen_path'] ?? null, 0);
            self::eliminarMediaSiHuerfana($db, $ej['imagen_demo_path'] ?? null, 0);
            jsonOut(true, 'Ejercicio eliminado de tu catálogo.');
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[CatalogoService::apiBorrar] ' . $e->getMessage());
            jsonOut(false, 'No se pudo eliminar el ejercicio.');
        }
    }

    /**
     * Propaga cambios de nombre/grupo/implemento/indicaciones únicamente a rutinas/plantillas dentro del alcance.
     */
    private static function propagarCambioARutinasYPlantillas(
        PDO $db,
        ?int $ownerEntId,
        int $globalOriginId,
        string $nombreAnterior,
        string $nombre,
        string $grupo,
        string $tipoImp,
        string $unidadCarga,
        string $indicaciones
    ): void {
        try {
            if ($ownerEntId !== null && $ownerEntId > 0) {
                $db->prepare("
                    UPDATE ejercicios e
                    JOIN rutinas r ON r.id = e.rutina_id
                    SET e.nombre = ?, e.grupo_muscular = ?, e.tipo_implemento = ?, e.unidad_carga = ?,
                        e.nota_biomecanica = CASE WHEN ? != '' THEN ? ELSE e.nota_biomecanica END
                    WHERE r.entrenador_id = ?
                      AND LOWER(TRIM(e.nombre)) = LOWER(TRIM(?))
                      AND r.estado IN ('en_progreso', 'pendiente')
                ")->execute([$nombre, $grupo, $tipoImp, $unidadCarga, $indicaciones, $indicaciones, $ownerEntId, $nombreAnterior]);

                $db->prepare("
                    UPDATE plantilla_ejercicios pe
                    JOIN plantillas_rutinas p ON p.id = pe.plantilla_id
                    SET pe.nombre = ?, pe.tipo_implemento = ?, pe.unidad_carga = ?,
                        pe.nota_biomecanica = CASE WHEN ? != '' THEN ? ELSE pe.nota_biomecanica END
                    WHERE p.entrenador_id = ?
                      AND LOWER(TRIM(pe.nombre)) = LOWER(TRIM(?))
                ")->execute([$nombre, $tipoImp, $unidadCarga, $indicaciones, $indicaciones, $ownerEntId, $nombreAnterior]);
            } else {
                // Global: no sobrescribir a entrenadores que ya personalizaron u ocultaron ese ejercicio
                $db->prepare("
                    UPDATE ejercicios e
                    JOIN rutinas r ON r.id = e.rutina_id
                    SET e.nombre = ?, e.grupo_muscular = ?, e.tipo_implemento = ?, e.unidad_carga = ?,
                        e.nota_biomecanica = CASE WHEN ? != '' THEN ? ELSE e.nota_biomecanica END
                    WHERE LOWER(TRIM(e.nombre)) = LOWER(TRIM(?))
                      AND r.estado IN ('en_progreso', 'pendiente')
                      AND NOT EXISTS (
                          SELECT 1 FROM catalogo_ejercicios ov
                          WHERE ov.entrenador_id = r.entrenador_id AND ov.origen_global_id = ?
                      )
                ")->execute([$nombre, $grupo, $tipoImp, $unidadCarga, $indicaciones, $indicaciones, $nombreAnterior, $globalOriginId]);

                $db->prepare("
                    UPDATE plantilla_ejercicios pe
                    JOIN plantillas_rutinas p ON p.id = pe.plantilla_id
                    SET pe.nombre = ?, pe.tipo_implemento = ?, pe.unidad_carga = ?,
                        pe.nota_biomecanica = CASE WHEN ? != '' THEN ? ELSE pe.nota_biomecanica END
                    WHERE LOWER(TRIM(pe.nombre)) = LOWER(TRIM(?))
                      AND NOT EXISTS (
                          SELECT 1 FROM catalogo_ejercicios ov
                          WHERE ov.entrenador_id = p.entrenador_id AND ov.origen_global_id = ?
                      )
                ")->execute([$nombre, $tipoImp, $unidadCarga, $indicaciones, $indicaciones, $nombreAnterior, $globalOriginId]);
            }
        } catch (\Throwable $e) {
            error_log('[CatalogoService::propagar] ' . $e->getMessage());
        }
    }
}
