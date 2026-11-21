<?php
/**
 * core/ImportadorService.php
 * Single Source of Truth (SSOT) para el Importador Inteligente de Historial Deportivo (Gemini AI).
 * Usado tanto por EntrenadorController (acotado a sus atletas) como por AdminController (global Super Admin).
 */

class ImportadorService {

    /**
     * Analiza un documento deportivo (.xlsx, .docx, .pdf, .csv, .txt) con extracción tabular / Gemini AI.
     *
     * @param PDO      $db
     * @param int|null $entScopeId null = Super Admin (acceso a cualquier cliente); >0 = solo atletas del entrenador.
     */
    public static function analizar(PDO $db, ?int $entScopeId = null): void {
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');
        @ini_set('memory_limit', '256M');
        ob_start();

        header('Content-Type: application/json; charset=utf-8');
        csrfCheck();

        $clienteId = (int)($_POST['cliente_id'] ?? 0);
        if ($clienteId <= 0) {
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, 'Debes seleccionar un atleta válido para asociar el historial.', [], 400);
        }

        if ($entScopeId !== null) {
            $stAtl = $db->prepare("
                SELECT c.id, c.entrenador_id, c.peso_kg, u.nombre, u.email
                FROM clientes c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.id = ? AND c.entrenador_id = ?
                LIMIT 1
            ");
            $stAtl->execute([$clienteId, $entScopeId]);
        } else {
            $stAtl = $db->prepare("
                SELECT c.id, c.entrenador_id, c.peso_kg, u.nombre, u.email
                FROM clientes c
                JOIN usuarios u ON c.usuario_id = u.id
                WHERE c.id = ?
                LIMIT 1
            ");
            $stAtl->execute([$clienteId]);
        }

        $atleta = $stAtl->fetch(PDO::FETCH_ASSOC);
        if (!$atleta) {
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, 'Atleta no encontrado o sin permisos suficientes.', [], 403);
        }

        $adminUserId = (int)($_SESSION['user_id'] ?? 0);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $errCode = $_FILES['archivo']['error'] ?? -1;
            $msgErr = match ($errCode) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'El archivo supera el tamaño máximo permitido.',
                UPLOAD_ERR_NO_FILE                        => 'No se subió ningún archivo.',
                default                                   => 'Error al transferir el archivo al servidor (código ' . $errCode . ').'
            };
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, $msgErr, [], 400);
        }

        $file     = $_FILES['archivo'];
        $maxBytes = 20 * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, 'El archivo excede el límite máximo de 20 MB.', [], 400);
        }

        $origName    = (string)$file['name'];
        $ext         = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        $allowedExts = ['xlsx', 'xls', 'docx', 'pdf', 'csv', 'txt'];
        if (!in_array($ext, $allowedExts, true)) {
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, "Extensión no permitida (.{$ext}). Formatos aceptados: Excel (.xlsx, .csv), Word (.docx) o PDF (.pdf).", [], 400);
        }

        try {
            $parsedDoc = DocumentParser::parse($file['tmp_name'], $origName);

            $catalogoScopeId = $entScopeId;
            if ($catalogoScopeId === null && !empty($atleta['entrenador_id']) && (int)$atleta['entrenador_id'] !== $adminUserId) {
                $catalogoScopeId = (int)$atleta['entrenador_id'];
            }
            $catalogoEjercicios = CatalogoService::obtenerEjerciciosVisibles($db, $catalogoScopeId);

            $resultadoIA = null;
            if (in_array($ext, ['xlsx', 'xls'], true)) {
                $resultadoIA = DocumentParser::extraerCronologiaTabular($file['tmp_name'], $catalogoEjercicios);
            }

            if (empty($resultadoIA) || empty($resultadoIA['sesiones'])) {
                $resultadoIA = GeminiService::analizarHistorialAtleta(
                    $parsedDoc,
                    $catalogoEjercicios,
                    $atleta['nombre'],
                    date('Y-m-d')
                );
            }

            $catalogoList = array_map(static fn(array $c): array => [
                'id'           => (int)$c['id'],
                'nombre'       => $c['nombre'],
                'grupo'        => $c['grupo_muscular'] ?? '',
                'equipamiento' => $c['equipamiento'] ?? '',
                'implemento'   => $c['tipo_implemento'] ?? 'barra',
            ], $catalogoEjercicios);

            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(true, 'Archivo analizado exitosamente con Gemini AI.', [
                'cronologia' => $resultadoIA,
                'catalogo'   => $catalogoList,
                'atleta'     => [
                    'id'     => (int)$atleta['id'],
                    'nombre' => $atleta['nombre'],
                ],
                'archivo'    => [
                    'nombre'  => $origName,
                    'formato' => $ext,
                    'tamano'  => $file['size'],
                ],
            ]);
        } catch (\Throwable $e) {
            while (ob_get_level() > 0) ob_end_clean();
            error_log('[ImportadorService::analizar] ' . $e->getMessage());
            jsonOut(false, 'Error procesando documento con IA: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Persiste en una transacción ACID las sesiones, rutinas actuales y medidas corporales importadas.
     */
    public static function guardar(PDO $db, ?int $entScopeId = null): void {
        @set_time_limit(180);
        @ini_set('max_execution_time', '180');
        ob_start();

        header('Content-Type: application/json; charset=utf-8');
        csrfCheck();

        $payload = getJsonPayload();
        if (empty($payload)) {
            $payload = $_POST;
        }

        $clienteId = (int)($payload['cliente_id'] ?? 0);
        if ($clienteId <= 0) {
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, 'Cliente inválido o no especificado.', [], 400);
        }

        if ($entScopeId !== null) {
            $stAtl = $db->prepare("SELECT id, entrenador_id FROM clientes WHERE id = ? AND entrenador_id = ? LIMIT 1");
            $stAtl->execute([$clienteId, $entScopeId]);
        } else {
            $stAtl = $db->prepare("SELECT id, entrenador_id FROM clientes WHERE id = ? LIMIT 1");
            $stAtl->execute([$clienteId]);
        }
        $atlRow = $stAtl->fetch(PDO::FETCH_ASSOC);
        if (!$atlRow) {
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, 'Atleta no encontrado o sin permisos suficientes.', [], 403);
        }

        $sessionUserId = (int)($_SESSION['user_id'] ?? 0);
        $rutinaEntId   = $entScopeId ?? (!empty($atlRow['entrenador_id']) ? (int)$atlRow['entrenador_id'] : $sessionUserId);
        $catalogOwner  = ($entScopeId !== null)
            ? $entScopeId
            : (($rutinaEntId > 0 && $rutinaEntId !== $sessionUserId) ? $rutinaEntId : null);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $sesiones = $payload['sesiones'] ?? [];
        $medidas  = $payload['medidas_corporales'] ?? [];

        if (empty($sesiones) && empty($medidas)) {
            while (ob_get_level() > 0) ob_end_clean();
            jsonOut(false, 'No se proporcionaron sesiones ni medidas para registrar en el historial.', [], 400);
        }

        $db->beginTransaction();
        try {
            $totalSesiones = 0;
            $totalSeries   = 0;
            $totalPesos    = 0;

            $stRut = $db->prepare("SELECT id FROM rutinas WHERE cliente_id = ? AND titulo = 'Historial Importado' LIMIT 1");
            $stRut->execute([$clienteId]);
            $rutinaContenedorId = (int)$stRut->fetchColumn();

            if (!$rutinaContenedorId) {
                $stInsRut = $db->prepare("
                    INSERT INTO rutinas (cliente_id, entrenador_id, titulo, descripcion, estado, dias_semana)
                    VALUES (?, ?, 'Historial Importado', 'Contenedor de sesiones y cronología importada con IA', 'archivada', '')
                ");
                $stInsRut->execute([$clienteId, $rutinaEntId]);
                $rutinaContenedorId = (int)$db->lastInsertId();
            }

            $ultimosPesosPorEjercicio = [];
            foreach ($sesiones as $sHist) {
                foreach ($sHist['ejercicios'] ?? [] as $ejHist) {
                    $pRaw      = trim((string)($ejHist['descripcion_peso'] ?? ''));
                    $firstPeso = (float)($ejHist['series'][0]['peso'] ?? 0);
                    if ($pRaw !== '' && $firstPeso > 0) {
                        $nomKey = mb_strtolower(trim((string)($ejHist['nombre_oficial'] ?? $ejHist['nombre_detectado'] ?? '')));
                        $ultimosPesosPorEjercicio[$nomKey] = [
                            'descripcion_peso' => $pRaw,
                            'series'           => $ejHist['series'] ?? [],
                            'unidad_carga'     => $ejHist['unidad_carga'] ?? 'kg',
                            'equipamiento'     => $ejHist['equipamiento'] ?? null,
                            'tipo_implemento'  => $ejHist['tipo_implemento'] ?? 'barra',
                        ];
                    }
                }
            }

            $fechasValidas = [];
            foreach ($sesiones as $s) {
                $f = trim((string)($s['fecha'] ?? ''));
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) {
                    $fechasValidas[] = $f;
                }
            }

            $mapRutinasCreadas      = [];
            $mapRutinasPorNombre    = [];
            $mapEjCreados           = [];
            $rutinaActivaPreservada = false;
            $maxFecha               = !empty($fechasValidas) ? max($fechasValidas) : '';

            if (!empty($fechasValidas)) {
                $lunesUltimaSemana = date('Y-m-d', strtotime('monday this week', strtotime($maxFecha)));
                $limiteReciente    = date('Y-m-d', strtotime('-6 days', strtotime($maxFecha)));
                $criterioCorte     = min($lunesUltimaSemana, $limiteReciente);

                $sesionesProgramaActual = [];
                foreach ($sesiones as $s) {
                    $f = trim((string)($s['fecha'] ?? ''));
                    if ($f >= $criterioCorte && !empty($s['ejercicios'])) {
                        $sesionesProgramaActual[] = $s;
                    }
                }

                if (count($sesionesProgramaActual) < 2 && count($sesiones) > 0) {
                    $sesionesProgramaActual = array_slice($sesiones, -min(5, count($sesiones)));
                }

                $stRutActivas = $db->prepare("
                    SELECT id, titulo, creado_en
                    FROM rutinas
                    WHERE cliente_id = ? AND estado = 'en_progreso'
                    ORDER BY creado_en DESC, id DESC
                ");
                $stRutActivas->execute([$clienteId]);
                $rutinasActivasExistentes = $stRutActivas->fetchAll(PDO::FETCH_ASSOC);

                $debeActivarNuevaRutina = true;
                if (!empty($rutinasActivasExistentes)) {
                    $fechaCreacionActiva = date('Y-m-d', strtotime($rutinasActivasExistentes[0]['creado_en'] ?? 'now'));

                    $stUltimaActividad = $db->prepare("SELECT MAX(fecha) FROM sesion_series WHERE cliente_id = ?");
                    $stUltimaActividad->execute([$clienteId]);
                    $ultimaFechaActividad = (string)($stUltimaActividad->fetchColumn() ?: '1970-01-01');

                    $fechaReferenciaSistema = max($fechaCreacionActiva, $ultimaFechaActividad);
                    if ($maxFecha <= $fechaReferenciaSistema) {
                        $debeActivarNuevaRutina = false;
                        $rutinaActivaPreservada = true;
                    }
                }

                if ($debeActivarNuevaRutina && !empty($sesionesProgramaActual)) {
                    $db->prepare("UPDATE rutinas SET estado = 'archivada' WHERE cliente_id = ? AND estado = 'en_progreso'")
                       ->execute([$clienteId]);

                    $stInsActiva = $db->prepare("
                        INSERT INTO rutinas (cliente_id, entrenador_id, titulo, descripcion, dias_semana, estado)
                        VALUES (?, ?, ?, ?, ?, 'en_progreso')
                    ");

                    $stInsEj = $db->prepare("
                        INSERT INTO ejercicios (rutina_id, nombre, series, repeticiones, orden, rir, tipo_implemento, unidad_carga, descripcion, grupo_muscular, nota_biomecanica)
                        VALUES (?, ?, ?, ?, ?, 2, ?, ?, ?, ?, ?)
                    ");

                    $diasProcesados = [];
                    foreach ($sesionesProgramaActual as $sProg) {
                        $fProg = trim((string)($sProg['fecha'] ?? ''));
                        $rNom  = trim((string)($sProg['rutina_nombre'] ?? 'Rutina'));
                        $dow   = (string)date('N', strtotime($fProg));

                        if (isset($diasProcesados[$dow])) {
                            continue;
                        }
                        $diasProcesados[$dow] = true;

                        $descRut = "Rutina activa importada ({$rNom})";
                        $stInsActiva->execute([$clienteId, $rutinaEntId, $rNom, $descRut, $dow]);
                        $newRutId = (int)$db->lastInsertId();

                        $stInsPlImp = $db->prepare("INSERT INTO plantillas_rutinas (entrenador_id, titulo) VALUES (?, ?)");
                        $stInsPlImp->execute([$rutinaEntId, $rNom]);
                        $newPlId = (int)$db->lastInsertId();
                        $db->prepare("UPDATE rutinas SET plantilla_id = ? WHERE id = ?")->execute([$newPlId, $newRutId]);

                        $mapRutinasCreadas[$fProg]  = $newRutId;
                        $mapRutinasPorNombre[$rNom] = $newRutId;

                        $ejListProg = $sProg['ejercicios'] ?? [];
                        foreach ($ejListProg as $oIdx => $ejP) {
                            $ejNom    = trim((string)($ejP['nombre_oficial'] ?? $ejP['nombre_detectado'] ?? 'Ejercicio'));
                            $ejNomKey = mb_strtolower($ejNom);
                            $prevData = $ultimosPesosPorEjercicio[$ejNomKey] ?? null;

                            $ejImp = in_array($ejP['tipo_implemento'] ?? '', ['mancuerna', 'barra', 'polea_maquina', 'sin_peso'], true)
                                ? $ejP['tipo_implemento']
                                : ($prevData['tipo_implemento'] ?? 'barra');
                            $ejUni = in_array($ejP['unidad_carga'] ?? $ejP['unidad'] ?? '', ['kg', 'lbs', 'placas', 'ninguna'], true)
                                ? ($ejP['unidad_carga'] ?? $ejP['unidad'])
                                : ($prevData['unidad_carga'] ?? 'kg');
                            $ejGrp  = trim((string)($ejP['grupo_muscular'] ?? ''));
                            $ejEqp  = trim((string)($ejP['equipamiento'] ?? '')) ?: ($prevData['equipamiento'] ?? null);
                            $ejDesc = trim((string)($ejP['descripcion_peso'] ?? '')) ?: ($prevData['descripcion_peso'] ?? null);

                            if ($ejDesc !== null && ($ejUni === 'kg' || empty($ejP['unidad_carga']))) {
                                $descLower = mb_strtolower($ejDesc);
                                if (strpos($descLower, 'placa') !== false) {
                                    $ejUni = 'placas';
                                } elseif (strpos($descLower, 'lb') !== false) {
                                    $ejUni = 'lbs';
                                }
                            }

                            $seriesListToUse = (!empty($ejP['series']) && (float)($ejP['series'][0]['peso'] ?? 0) > 0)
                                ? $ejP['series']
                                : (!empty($prevData['series']) ? $prevData['series'] : ($ejP['series'] ?? []));

                            $numSeriesCalc = max(1, count($seriesListToUse));
                            $repsArr       = array_column($seriesListToUse, 'reps');
                            $repsStr       = !empty($repsArr) ? (string)end($repsArr) : '10';

                            $stInsEj->execute([
                                $newRutId,
                                $ejNom,
                                $numSeriesCalc,
                                $repsStr,
                                $oIdx + 1,
                                $ejImp,
                                $ejUni,
                                $ejDesc !== '' ? $ejDesc : null,
                                $ejGrp !== '' ? $ejGrp : null,
                                $ejEqp !== '' ? $ejEqp : null
                            ]);
                            $newEjId = (int)$db->lastInsertId();
                            $mapEjCreados[$newRutId][$ejNom] = $newEjId;

                            $stInsPlEjImp = $db->prepare("INSERT INTO plantilla_ejercicios (plantilla_id, nombre_override, orden, tipo_implemento, unidad_carga) VALUES (?, ?, ?, ?, ?)");
                            $stInsPlEjImp->execute([$newPlId, $ejNom, $oIdx + 1, $ejImp, $ejUni]);
                            $newPlEjId = (int)$db->lastInsertId();

                            $stInsPlSImp = $db->prepare("INSERT INTO plantilla_series (plantilla_ej_id, numero_serie, tipo_serie, kg_objetivo, reps_objetivo, rir) VALUES (?, ?, ?, ?, ?, ?)");
                            foreach ($seriesListToUse as $sIdx => $sData) {
                                $sKg  = isset($sData['peso']) && is_numeric($sData['peso']) && (float)$sData['peso'] > 0 ? (float)$sData['peso'] : null;
                                $sRep = isset($sData['reps']) && is_numeric($sData['reps']) ? (int)$sData['reps'] : (int)$repsStr;
                                $sRir = isset($sData['rir']) && is_numeric($sData['rir']) ? (int)$sData['rir'] : 2;
                                $stInsPlSImp->execute([$newPlEjId, $sIdx + 1, 'normal', $sKg, $sRep, $sRir]);
                            }

                            asegurarEjercicioEnCatalogo($db, $ejNom, $ejGrp, $ejImp, $ejUni, $ejEqp, $catalogOwner);
                        }
                    }
                }
            }

            $stFechasExistentes = $db->prepare("
                SELECT DISTINCT DATE(inicio_en) AS f_ini
                FROM sesiones_asistencia
                WHERE cliente_id = ?
            ");
            $stFechasExistentes->execute([$clienteId]);
            $fechasAsistenciaSet = array_flip($stFechasExistentes->fetchAll(PDO::FETCH_COLUMN));

            $asistenciasBatch = [];
            $seriesBatch      = [];
            $fechasAImportar  = [];

            foreach ($sesiones as $s) {
                $fecha = trim((string)($s['fecha'] ?? ''));
                if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
                    continue;
                }
                if (!empty($s['es_planificada_futura'])) {
                    continue;
                }
                $fechasAImportar[$fecha] = true;

                $rutinaNombre = trim((string)($s['rutina_nombre'] ?? 'Rutina Importada'));
                $rutinaAsocId = $mapRutinasCreadas[$fecha] ?? $mapRutinasPorNombre[$rutinaNombre] ?? $rutinaContenedorId;

                $diaInicio = $fecha . ' 09:00:00';
                $diaFin    = $fecha . ' 10:15:00';
                $notasAsis = 'Cronología importada: ' . mb_substr($rutinaNombre, 0, 70);

                if (!isset($fechasAsistenciaSet[$fecha])) {
                    $asistenciasBatch[] = [
                        $rutinaAsocId, $clienteId, $rutinaEntId, $diaInicio, $diaFin, 'completada', $notasAsis, 1
                    ];
                    $fechasAsistenciaSet[$fecha] = true;
                }
                $totalSesiones++;

                $ejercicios = $s['ejercicios'] ?? [];
                foreach ($ejercicios as $ej) {
                    if (isset($ej['tiene_datos']) && !$ej['tiene_datos']) {
                        continue;
                    }

                    $nombreEj = trim((string)($ej['nombre_oficial'] ?? $ej['nombre_detectado'] ?? 'Ejercicio'));
                    if ($nombreEj === '') {
                        $nombreEj = 'Ejercicio';
                    }

                    $ejercicioAsocId   = $mapEjCreados[$rutinaAsocId][$nombreEj] ?? null;
                    $equipamiento      = trim((string)($ej['equipamiento'] ?? ''));
                    $tipoImplemento    = in_array($ej['tipo_implemento'] ?? '', ['mancuerna', 'barra', 'polea_maquina', 'sin_peso'], true)
                        ? $ej['tipo_implemento'] : 'barra';
                    $unidadCargaGlobal = in_array($ej['unidad_carga'] ?? $ej['unidad'] ?? '', ['kg', 'lbs', 'placas', 'ninguna'], true)
                        ? ($ej['unidad_carga'] ?? $ej['unidad']) : 'kg';

                    $series = $ej['series'] ?? [];
                    foreach ($series as $idx => $sr) {
                        $numSerie  = max(1, (int)($sr['numero_serie'] ?? ($idx + 1)));
                        $reps      = max(1, min(255, (int)($sr['reps'] ?? 10)));
                        $peso      = max(0.0, min(999.0, (float)($sr['peso'] ?? 0.0)));
                        $tipoSerie = in_array($sr['tipo_serie'] ?? '', ['normal', 'calentamiento', 'fallo', 'drop'], true)
                            ? $sr['tipo_serie'] : 'normal';

                        $unidadCarga = in_array($sr['unidad'] ?? '', ['kg', 'lbs', 'placas', 'ninguna'], true)
                            ? $sr['unidad'] : $unidadCargaGlobal;

                        $kgReal = isset($sr['kg_real']) ? (float)$sr['kg_real'] : $peso;
                        if ($kgReal <= 0.0 && $peso > 0.0) {
                            $kgReal = ($unidadCarga === 'lbs') ? round($peso / 2.20462, 2) : $peso;
                        }

                        $pesoPorLado = isset($sr['peso_por_lado']) && $sr['peso_por_lado'] !== null
                            ? (float)$sr['peso_por_lado'] : null;

                        $seriesBatch[] = [
                            $clienteId,
                            $ejercicioAsocId,
                            $rutinaAsocId,
                            $rutinaNombre,
                            $nombreEj,
                            $equipamiento !== '' ? $equipamiento : null,
                            $fecha,
                            $numSerie,
                            $reps,
                            $peso,
                            $tipoSerie,
                            $tipoImplemento,
                            $unidadCarga,
                            $kgReal,
                            $pesoPorLado
                        ];
                    }
                }
            }

            if (!empty($asistenciasBatch)) {
                foreach (array_chunk($asistenciasBatch, 200) as $chunk) {
                    $ph   = [];
                    $vals = [];
                    foreach ($chunk as $row) {
                        $ph[] = '(?, ?, ?, ?, ?, ?, ?, ?)';
                        foreach ($row as $v) $vals[] = $v;
                    }
                    $sql = "INSERT INTO sesiones_asistencia
                        (rutina_id, cliente_id, entrenador_id, inicio_en, fin_en, estado, notas, verificada)
                        VALUES " . implode(', ', $ph);
                    $db->prepare($sql)->execute($vals);
                }
            }

            $fechasEliminar = array_keys($fechasAImportar);
            if (!empty($fechasEliminar)) {
                foreach (array_chunk($fechasEliminar, 200) as $fChunk) {
                    $phDates = implode(',', array_fill(0, count($fChunk), '?'));
                    $params  = array_merge([$clienteId], $fChunk);
                    $db->prepare("DELETE FROM sesion_series WHERE cliente_id = ? AND fecha IN ($phDates)")
                       ->execute($params);
                }
            }

            $seriesUnicas = [];
            foreach ($seriesBatch as $sRow) {
                $ejIdent = !empty($sRow[1]) ? (string)$sRow[1] : mb_strtolower(trim((string)$sRow[4]));
                $ukKey   = $sRow[0] . '|' . $sRow[6] . '|' . $ejIdent . '|' . $sRow[7];
                $seriesUnicas[$ukKey] = $sRow;
            }
            $seriesBatch = array_values($seriesUnicas);

            $totalSeries = count($seriesBatch);
            if (!empty($seriesBatch)) {
                foreach (array_chunk($seriesBatch, 250) as $chunk) {
                    $ph   = [];
                    $vals = [];
                    foreach ($chunk as $row) {
                        $ph[] = '(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?)';
                        foreach ($row as $v) $vals[] = $v;
                    }
                    $sql = "INSERT INTO sesion_series
                        (cliente_id, ejercicio_id, rutina_id, rutina_nombre, ejercicio_nombre, equipamiento,
                         fecha, numero_serie, reps_realizadas, peso_usado, completado, tipo_serie, tipo_implemento, unidad_carga, kg_real, peso_por_lado)
                        VALUES " . implode(', ', $ph) . "
                        ON DUPLICATE KEY UPDATE
                            reps_realizadas = VALUES(reps_realizadas),
                            peso_usado      = VALUES(peso_usado),
                            unidad_carga    = VALUES(unidad_carga),
                            tipo_implemento = VALUES(tipo_implemento),
                            kg_real         = VALUES(kg_real),
                            peso_por_lado   = VALUES(peso_por_lado),
                            completado      = 1";
                    $db->prepare($sql)->execute($vals);
                }
            }

            $pesosPorFecha = [];
            foreach ($medidas as $m) {
                $fMed = trim((string)($m['fecha'] ?? ''));
                $pMed = (float)($m['peso_kg'] ?? 0);
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fMed) && $pMed >= 20.0 && $pMed <= 350.0) {
                    $pesosPorFecha[$fMed] = $pMed;
                }
            }
            $pesosBatch = [];
            foreach ($pesosPorFecha as $fMed => $pMed) {
                $pesosBatch[] = [$clienteId, $pMed, $fMed, 'manual'];
            }
            $totalPesos = count($pesosBatch);
            if (!empty($pesosBatch)) {
                foreach (array_chunk($pesosBatch, 200) as $chunk) {
                    $ph   = [];
                    $vals = [];
                    foreach ($chunk as $row) {
                        $ph[] = '(?, ?, ?, ?)';
                        foreach ($row as $v) $vals[] = $v;
                    }
                    $sql = "INSERT INTO peso_historial (cliente_id, peso_kg, fecha, tipo)
                            VALUES " . implode(', ', $ph) . "
                            ON DUPLICATE KEY UPDATE peso_kg = VALUES(peso_kg), tipo = 'manual'";
                    $db->prepare($sql)->execute($vals);
                }

                ksort($pesosPorFecha);
                $ultimoPesoRegistrado = end($pesosPorFecha);
                if ($ultimoPesoRegistrado > 0) {
                    $db->prepare("UPDATE clientes SET peso_kg = ? WHERE id = ?")->execute([$ultimoPesoRegistrado, $clienteId]);
                }
            }

            $db->commit();
            while (ob_get_level() > 0) ob_end_clean();

            $msgFinal = $rutinaActivaPreservada
                ? "Cronología consolidada con éxito ({$totalSesiones} sesiones, {$totalSeries} series). Se preservó intacta la rutina activa que el atleta ya tenía asignada en la web."
                : "Cronología consolidada con éxito. Se registraron {$totalSesiones} sesiones ({$totalSeries} series) en el historial y se activó el programa vigente ({$maxFecha}).";

            jsonOut(true, $msgFinal, [
                'total_sesiones'           => $totalSesiones,
                'total_series'             => $totalSeries,
                'total_pesos'              => $totalPesos,
                'rutina_activa_preservada' => $rutinaActivaPreservada,
            ]);
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            while (ob_get_level() > 0) ob_end_clean();
            error_log('[ImportadorService::guardar] ' . $e->getMessage());
            jsonOut(false, 'Error al guardar la cronología: ' . $e->getMessage(), [], 500);
        }
    }
}

