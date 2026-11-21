<?php
// controllers/AdminController.php
declare(strict_types=1);

requireRole('admin');
require_once BASE_DIR . '/models/UsuarioModel.php';
require_once BASE_DIR . '/core/progreso.php';

$action = $segment[1] ?? 'rutina';

match ($action) {
    'dashboard', 'rutina'                => adminRutina(),
    'gestion'                            => adminGestion(),
    'configuracion'                      => adminConfiguracion(),
    'api-dia-detalle'                    => adminApiDiaDetalle(),
    'api-crear-rutina-propia'            => adminApiCrearRutinaPropia(),
    'api-actualizar-rutina-completa'     => adminApiActualizarRutinaCompleta(),
    'api-eliminar-rutina'                => adminApiEliminarRutina(),
    'api-guardar-serie'                  => adminApiGuardarSerie(),
    'api-guardar-rutina-como-plantilla'  => adminApiGuardarRutinaComoPlantilla(),
    'api-listar-plantillas-catalogo'     => adminApiListarPlantillasCatalogo(),
    'api-crear-entrenador'               => adminApiCrearEntrenador(),
    'api-aprobar-entrenador'             => adminApiAprobarEntrenador(),
    'api-actualizar-membresia'           => adminApiActualizarMembresia(),
    'api-generar-codigo'                 => adminApiGenerarCodigo(),
    'api-toggle-pausa'                   => adminApiTogglePausa(),
    'api-reasignar-atleta'               => adminApiReasignarAtleta(),
    'api-revocar-entrenador'             => adminApiRevocarEntrenador(),
    'api-guardar-configuracion'          => adminApiGuardarConfiguracion(),
    'api-catalogo-list'                  => adminApiCatalogoList(),
    'api-catalogo-agregar'               => adminApiCatalogoAgregar(),
    'api-catalogo-editar'                => adminApiCatalogoEditar(),
    'api-catalogo-borrar'                => adminApiCatalogoBorrar(),
    'api-importar-historial-analizar'    => adminApiImportarHistorialAnalizar(),
    'api-importar-historial-guardar'     => adminApiImportarHistorialGuardar(),
    'descargar-respaldo-db'              => adminDescargarRespaldoDb(),
    'notificaciones'                     => adminGestion(),
    'api-notificaciones'                 => adminApiNotificaciones(),
    'api-marcar-notificacion'            => adminApiMarcarNotificacion(),
    'api-leer-notificaciones'            => adminApiLeerNotificaciones(),
    default                              => adminRutina(),
};

/**
 * Obtiene o inicializa el perfil de atleta personal exclusivo del Superadmin
 * para que pueda asignarse y registrar su propia rutina sin aparecer en las listas de atletas de entrenadores.
 */
function adminObtenerClienteIdPropio(PDO $db): int {
    $adminUserId = (int)($_SESSION['user_id'] ?? 0);
    if ($adminUserId <= 0) {
        return 0;
    }

    $st = $db->prepare("SELECT id, entrenador_id FROM clientes WHERE usuario_id = ? LIMIT 1");
    $st->execute([$adminUserId]);
    $row = $st->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $cid = (int)$row['id'];
        // Garantizar que el registro propio apunte al propio Superadmin como entrenador para aislarlo de terceros
        if ((int)($row['entrenador_id'] ?? 0) !== $adminUserId) {
            $db->prepare("UPDATE clientes SET entrenador_id = ? WHERE id = ?")->execute([$adminUserId, $cid]);
        }
        return $cid;
    }

    $db->prepare("
        INSERT INTO clientes (usuario_id, entrenador_id, peso_kg, altura_cm, objetivo)
        VALUES (?, ?, 75.0, 175.0, 'Entrenamiento Personal Superadmin')
    ")->execute([$adminUserId, $adminUserId]);

    return (int)$db->lastInsertId();
}

function adminObtenerDiasProgramados(PDO $db, int $clienteId): array {
    $st = $db->prepare("
        SELECT dias_semana
        FROM rutinas
        WHERE cliente_id = ?
          AND estado = 'en_progreso'
          AND (es_modificacion IS NULL OR es_modificacion = 0 OR estado_modificacion = 'aprobada')
    ");
    $st->execute([$clienteId]);
    $diasSet = [];
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $ds) {
        foreach (explode(',', (string)$ds) as $part) {
            $d = (int)trim($part);
            if ($d >= 1 && $d <= 7) {
                $diasSet[$d] = true;
            }
        }
    }
    $dias = array_keys($diasSet);
    sort($dias);
    return $dias;
}

function adminSanitizarCodigoReferido(string $raw, string $nombreFallback = 'COACH', int $idFallback = 1): string {
    $clean = strtoupper(trim($raw));
    $clean = preg_replace('/[^A-Z0-9\-_]/', '', $clean) ?? '';
    if (strlen($clean) < 3) {
        $slug = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nombreFallback) ?? '');
        $slug = substr($slug !== '' ? $slug : 'COACH', 0, 8);
        $clean = $slug . '-' . $idFallback;
    }
    return substr($clean, 0, 32);
}

// ══════════════════════════════════════════════════════════════════
//  MÓDULO 1: RUTINA PERSONAL DEL SUPERADMIN (ATLETA + ENTRENADOR)
// ══════════════════════════════════════════════════════════════════

function adminRutina(): void {
    $db = getDB();
    $adminUserId    = (int)($_SESSION['user_id'] ?? 0);
    $adminClienteId = adminObtenerClienteIdPropio($db);

    $diasProgramados = adminObtenerDiasProgramados($db, $adminClienteId);

    // Catálogo de ejercicios globales para el selector / autocompletado
    $catalogoEjercicios = CatalogoService::obtenerEjerciciosVisibles($db, null);

    // Plantillas guardadas (propias o del catálogo)
    $stPl = $db->prepare("
        SELECT p.id, p.titulo, p.descripcion, p.cardio_activo, p.cardio_tiempo, p.cardio_indicaciones,
               COUNT(pe.id) AS total_ejercicios
        FROM plantillas_rutinas p
        LEFT JOIN plantilla_ejercicios pe ON pe.plantilla_id = p.id
        WHERE p.es_catalogo = 1
        GROUP BY p.id
        ORDER BY p.id DESC
    ");
    $stPl->execute();
    $plantillasCatalogo = $stPl->fetchAll(PDO::FETCH_ASSOC);

    $pageTitle = 'Mi Rutina — Superadmin';
    require BASE_DIR . '/views/admin/rutina.php';
}

function adminApiDiaDetalle(): void {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    try {
        $db = getDB();
        $adminClienteId = adminObtenerClienteIdPropio($db);
        $fecha = trim((string)($_GET['fecha'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d');
        }

        $resultado = obtenerDetalleDia($db, $adminClienteId, $fecha, 'entrenador');
        $resultado['dias_programados'] = adminObtenerDiasProgramados($db, $adminClienteId);
        $resultado['cliente_id']       = $adminClienteId;

        $status = $resultado['status'] ?? ($resultado['ok'] ? 200 : 400);
        http_response_code((int)$status);
        echo json_encode(
            $resultado,
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );
    } catch (\Throwable $e) {
        error_log('[adminApiDiaDetalle] ' . $e->getMessage());
        http_response_code(500);
        echo json_encode(['ok' => false, 'msg' => 'Error interno al consultar la rutina del día.']);
    }
    exit;
}

function adminApiCrearRutinaPropia(): void {
    csrfCheck();
    header('Content-Type: application/json; charset=utf-8');

    $db = getDB();
    $adminUserId    = (int)($_SESSION['user_id'] ?? 0);
    $adminClienteId = adminObtenerClienteIdPropio($db);
    $in = array_merge($_POST, getJsonPayload());

    $titulo      = trim(sanitizeInput((string)($in['titulo'] ?? 'Mi Rutina'), 120));
    if ($titulo === '') $titulo = 'Mi Rutina';
    $descripcion = trim(sanitizeInput((string)($in['descripcion'] ?? $titulo), 255));

    $diasInput = $in['dias_semana'] ?? $in['dias'] ?? [];
    if (is_string($diasInput)) {
        $diasInput = explode(',', $diasInput);
    }
    $diasLimpios = [];
    foreach ((array)$diasInput as $d) {
        $dn = (int)$d;
        if ($dn >= 1 && $dn <= 7) {
            $diasLimpios[$dn] = $dn;
        }
    }
    if (empty($diasLimpios)) {
        $diasLimpios[(int)date('N')] = (int)date('N');
    }
    ksort($diasLimpios);
    $diasStr = implode(',', $diasLimpios);

    $cardioActivo       = !empty($in['cardio_activo']) ? 1 : 0;
    $cardioTiempo       = $cardioActivo && !empty($in['cardio_tiempo']) ? max(1, min(300, (int)$in['cardio_tiempo'])) : null;
    $cardioIndicaciones = $cardioActivo ? trim((string)($in['cardio_indicaciones'] ?? '')) : '';

    $ejercicios = $in['ejercicios'] ?? [];
    if (!is_array($ejercicios) || empty($ejercicios)) {
        jsonOut(false, 'Agrega al menos un ejercicio a la rutina.');
    }

    try {
        $db->beginTransaction();

        // Si ya existe otra rutina activa en alguno de esos días para el Superadmin, quitarle esos días o archivarla si queda sin días
        $stAct = $db->prepare("
            SELECT id, dias_semana
            FROM rutinas
            WHERE cliente_id = ? AND estado = 'en_progreso'
        ");
        $stAct->execute([$adminClienteId]);
        foreach ($stAct->fetchAll(PDO::FETCH_ASSOC) as $rOld) {
            $oldId = (int)$rOld['id'];
            $oldDias = array_filter(array_map('intval', explode(',', (string)$rOld['dias_semana'])));
            $restantes = array_values(array_diff($oldDias, $diasLimpios));
            if (count($restantes) !== count($oldDias)) {
                if (empty($restantes)) {
                    $db->prepare("UPDATE rutinas SET estado = 'archivada' WHERE id = ?")->execute([$oldId]);
                } else {
                    $db->prepare("UPDATE rutinas SET dias_semana = ? WHERE id = ?")->execute([implode(',', $restantes), $oldId]);
                }
            }
        }

        // Crear plantilla vinculada
        $stPl = $db->prepare("
            INSERT INTO plantillas_rutinas (entrenador_id, titulo, descripcion, es_catalogo, cardio_activo, cardio_tiempo, cardio_indicaciones)
            VALUES (?, ?, ?, 0, ?, ?, ?)
        ");
        $stPl->execute([$adminUserId, $titulo, $descripcion, $cardioActivo, $cardioTiempo, $cardioIndicaciones]);
        $plantillaId = (int)$db->lastInsertId();

        // Crear rutina activa
        $stRut = $db->prepare("
            INSERT INTO rutinas (cliente_id, entrenador_id, plantilla_id, titulo, descripcion, dias_semana, estado, cardio_activo, cardio_tiempo, cardio_indicaciones)
            VALUES (?, ?, ?, ?, ?, ?, 'en_progreso', ?, ?, ?)
        ");
        $stRut->execute([
            $adminClienteId,
            $adminUserId,
            $plantillaId,
            $titulo,
            $descripcion,
            $diasStr,
            $cardioActivo,
            $cardioTiempo,
            $cardioIndicaciones,
        ]);
        $rutinaId = (int)$db->lastInsertId();

        $stInsPlEj = $db->prepare("
            INSERT INTO plantilla_ejercicios (plantilla_id, nombre_override, orden, descanso_segundos, tipo_implemento, unidad_carga)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stInsPlS = $db->prepare("
            INSERT INTO plantilla_series (plantilla_ej_id, numero_serie, tipo_serie, kg_objetivo, reps_objetivo, rir)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stInsEj = $db->prepare("
            INSERT INTO ejercicios
            (rutina_id, nombre, series, repeticiones, rir, orden, tipo_implemento, unidad_carga, descripcion, grupo_muscular, equipamiento, imagen_path, imagen_demo_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $orden = 1;
        foreach ($ejercicios as $ej) {
            $nombre = trim((string)($ej['nombre'] ?? ''));
            if ($nombre === '') continue;

            $seriesRaw = $ej['series_detalle'] ?? $ej['series'] ?? [];
            if (!is_array($seriesRaw)) $seriesRaw = [];
            $seriesCount = max(1, min(30, count($seriesRaw) > 0 ? count($seriesRaw) : (int)($ej['series'] ?? 3)));
            $reps = trim((string)($ej['repeticiones'] ?? '10'));
            if ($reps === '') $reps = '10';
            $rirVal = isset($ej['rir']) && $ej['rir'] !== '' && $ej['rir'] !== null ? max(0, min(5, (int)$ej['rir'])) : null;

            $imp = trim((string)($ej['tipo_implemento'] ?? 'barra'));
            if (!in_array($imp, ['mancuerna', 'barra', 'polea_maquina', 'sin_peso'], true)) {
                $imp = 'barra';
            }
            $unidad = trim((string)($ej['unidad_carga'] ?? 'kg'));
            if (!in_array($unidad, ['kg', 'lbs', 'placas', 'ninguna'], true)) {
                $unidad = ($imp === 'sin_peso') ? 'ninguna' : 'kg';
            }

            $descanso = isset($ej['descanso']) && is_numeric($ej['descanso']) ? (int)$ej['descanso'] : null;
            $stInsPlEj->execute([$plantillaId, $nombre, $orden, $descanso, $imp, $unidad]);
            $plEjId = (int)$db->lastInsertId();

            $pesosValores = [];
            if (!empty($seriesRaw)) {
                foreach ($seriesRaw as $sIdx => $sData) {
                    $sNum  = (int)($sData['numero_serie'] ?? ($sData['numero'] ?? ($sIdx + 1)));
                    $sTipo = strtolower(trim((string)($sData['tipo_serie'] ?? ($sData['tipo'] ?? 'normal'))));
                    if (!in_array($sTipo, ['normal', 'calentamiento', 'fallo', 'drop'], true)) {
                        $sTipo = 'normal';
                    }
                    $sKg   = isset($sData['peso']) && $sData['peso'] !== '' && $sData['peso'] !== null && is_numeric($sData['peso']) ? (float)$sData['peso'] : null;
                    if ($imp === 'sin_peso') $sKg = null;
                    $sRep  = isset($sData['reps']) && is_numeric($sData['reps']) ? (int)$sData['reps'] : (int)$reps;
                    $sRir  = isset($sData['rir']) && $sData['rir'] !== '' && $sData['rir'] !== null && is_numeric($sData['rir']) ? max(0, min(5, (int)$sData['rir'])) : $rirVal;
                    $stInsPlS->execute([$plEjId, $sNum, $sTipo, $sKg, $sRep, $sRir]);
                    if ($sKg !== null) {
                        $pesosValores[] = $sKg;
                    }
                }
            } else {
                $pesoVal = isset($ej['peso']) && $ej['peso'] !== '' && $ej['peso'] !== null ? (float)$ej['peso'] : null;
                if ($imp === 'sin_peso') $pesoVal = null;
                for ($s = 1; $s <= $seriesCount; $s++) {
                    $stInsPlS->execute([$plEjId, $s, 'normal', $pesoVal, (int)$reps, $rirVal]);
                    if ($pesoVal !== null) {
                        $pesosValores[] = $pesoVal;
                    }
                }
            }

            if ($imp === 'sin_peso' || $unidad === 'ninguna') {
                $desc = 'Sin peso';
            } elseif (!empty($pesosValores)) {
                $unicos = array_unique($pesosValores);
                $uniSuffix = ($unidad === 'placas') ? 'placas' : ($unidad === 'lbs' ? 'lbs' : 'kg');
                $desc = count($unicos) === 1 ? "{$pesosValores[0]} {$uniSuffix}" : (implode('-', $pesosValores) . " {$uniSuffix}");
            } else {
                $desc = '';
            }

            $cat = CatalogoService::obtenerMetaPorNombre($db, $nombre, null);

            $grupo       = $cat['grupo_muscular'] ?? inferirGrupoMuscularProgreso($nombre);
            $equip       = $cat['equipamiento'] ?? inferirEquipamientoProgreso($nombre, $imp);
            $imgPath     = $cat['imagen_path'] ?? null;
            $imgDemoPath = $cat['imagen_demo_path'] ?? null;

            if (!$cat) {
                asegurarEjercicioEnCatalogo($db, $nombre, $grupo, $imp, $unidad, $equip, null);
            }

            $stInsEj->execute([
                $rutinaId, $nombre, $seriesCount, $reps, $rirVal, $orden,
                $imp, $unidad, $desc, $grupo, $equip, $imgPath, $imgDemoPath
            ]);
            $orden++;
        }

        $db->commit();
        jsonOut(true, 'Rutina creada y asignada exitosamente.', [
            'rutina_id'        => $rutinaId,
            'dias_programados' => adminObtenerDiasProgramados($db, $adminClienteId),
        ]);
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiCrearRutinaPropia] ' . $e->getMessage());
        jsonOut(false, 'Error interno al crear la rutina.', [], 500);
    }
}

function adminApiActualizarRutinaCompleta(): void {
    csrfCheck();
    header('Content-Type: application/json; charset=utf-8');

    $db = getDB();
    $adminUserId    = (int)($_SESSION['user_id'] ?? 0);
    $adminClienteId = adminObtenerClienteIdPropio($db);
    $payload = array_merge($_POST, getJsonPayload());

    $rutinaId = (int)($payload['rutina_id'] ?? 0);
    if ($rutinaId <= 0) {
        jsonOut(false, 'Identificador de rutina requerido.', [], 400);
    }

    $chk = $db->prepare("SELECT id, plantilla_id, titulo, dias_semana FROM rutinas WHERE id = ? AND cliente_id = ? LIMIT 1");
    $chk->execute([$rutinaId, $adminClienteId]);
    $rutRow = $chk->fetch(PDO::FETCH_ASSOC);
    if (!$rutRow) {
        jsonOut(false, 'Rutina no encontrada.', [], 404);
    }

    $tituloNuevo = trim(sanitizeInput((string)($payload['titulo'] ?? ($rutRow['titulo'] ?? '')), 120));
    if ($tituloNuevo === '') {
        $tituloNuevo = (string)($rutRow['titulo'] ?: 'Mi Rutina');
    }
    $descNueva = trim(sanitizeInput((string)($payload['descripcion'] ?? $tituloNuevo), 255));

    $cardioActivo       = !empty($payload['cardio_activo']) ? 1 : 0;
    $cardioTiempo       = $cardioActivo && !empty($payload['cardio_tiempo']) ? max(1, min(300, (int)$payload['cardio_tiempo'])) : null;
    $cardioIndicaciones = $cardioActivo ? trim((string)($payload['cardio_indicaciones'] ?? '')) : '';

    // Permitir actualizar también los días asignados si se envían
    $diasStr = (string)($rutRow['dias_semana'] ?? '');
    $dClean = [];
    if (!empty($payload['dias_semana'])) {
        $dArr = is_array($payload['dias_semana']) ? $payload['dias_semana'] : explode(',', (string)$payload['dias_semana']);
        foreach ($dArr as $d) {
            $dn = (int)$d;
            if ($dn >= 1 && $dn <= 7) $dClean[$dn] = $dn;
        }
        if (!empty($dClean)) {
            ksort($dClean);
            $diasStr = implode(',', $dClean);
        }
    }

    $ejercicios = $payload['ejercicios'] ?? [];
    if (!is_array($ejercicios)) $ejercicios = [];

    try {
        $db->beginTransaction();

        // Resolver colisiones de días con otras rutinas activas del Super Admin (1 rutina por día)
        if (!empty($dClean)) {
            $stOtras = $db->prepare("
                SELECT id, dias_semana FROM rutinas
                WHERE cliente_id = ? AND id != ? AND estado != 'completada' AND estado != 'archivada'
                  AND (es_modificacion IS NULL OR es_modificacion = 0 OR estado_modificacion = 'aprobada')
            ");
            $stOtras->execute([$adminClienteId, $rutinaId]);
            foreach ($stOtras->fetchAll(PDO::FETCH_ASSOC) as $otra) {
                $oDias = array_values(array_filter(array_map('intval', explode(',', (string)$otra['dias_semana'])), static fn($x) => $x >= 1 && $x <= 7));
                $restantes = array_values(array_diff($oDias, array_values($dClean)));
                if (count($restantes) !== count($oDias)) {
                    if (empty($restantes)) {
                        $db->prepare("UPDATE rutinas SET estado = 'archivada', dias_semana = '' WHERE id = ?")->execute([(int)$otra['id']]);
                    } else {
                        sort($restantes);
                        $db->prepare("UPDATE rutinas SET dias_semana = ? WHERE id = ?")->execute([implode(',', $restantes), (int)$otra['id']]);
                    }
                }
            }
        }

        $db->prepare("
            UPDATE rutinas
            SET titulo = ?, descripcion = ?, cardio_activo = ?, cardio_tiempo = ?, cardio_indicaciones = ?, dias_semana = ?
            WHERE id = ?
        ")->execute([$tituloNuevo, $descNueva, $cardioActivo, $cardioTiempo, $cardioIndicaciones, $diasStr, $rutinaId]);

        $plantillaId = (int)($rutRow['plantilla_id'] ?? 0);
        if ($plantillaId <= 0) {
            $stInsPl = $db->prepare("INSERT INTO plantillas_rutinas (entrenador_id, titulo, cardio_activo, cardio_tiempo, cardio_indicaciones) VALUES (?, ?, ?, ?, ?)");
            $stInsPl->execute([$adminUserId, $tituloNuevo, $cardioActivo, $cardioTiempo, $cardioIndicaciones]);
            $plantillaId = (int)$db->lastInsertId();
            $db->prepare("UPDATE rutinas SET plantilla_id = ? WHERE id = ?")->execute([$plantillaId, $rutinaId]);
        } else {
            $db->prepare("UPDATE plantillas_rutinas SET titulo = ?, cardio_activo = ?, cardio_tiempo = ?, cardio_indicaciones = ? WHERE id = ?")
               ->execute([$tituloNuevo, $cardioActivo, $cardioTiempo, $cardioIndicaciones, $plantillaId]);
        }

        $db->prepare("DELETE FROM plantilla_series WHERE plantilla_ej_id IN (SELECT id FROM plantilla_ejercicios WHERE plantilla_id = ?)")->execute([$plantillaId]);
        $db->prepare("DELETE FROM plantilla_ejercicios WHERE plantilla_id = ?")->execute([$plantillaId]);

        $stInsPlEj = $db->prepare("
            INSERT INTO plantilla_ejercicios (plantilla_id, nombre_override, orden, descanso_segundos, tipo_implemento, unidad_carga)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stInsPlS = $db->prepare("
            INSERT INTO plantilla_series (plantilla_ej_id, numero_serie, tipo_serie, kg_objetivo, reps_objetivo, rir)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $idsMantener = [];
        foreach ($ejercicios as $ej) {
            $eid = (int)($ej['id'] ?? 0);
            if ($eid > 0) $idsMantener[] = $eid;
        }

        if (!empty($idsMantener)) {
            $inClause = implode(',', array_fill(0, count($idsMantener), '?'));
            $stDel = $db->prepare("DELETE FROM ejercicios WHERE rutina_id = ? AND id NOT IN ($inClause)");
            $stDel->execute(array_merge([$rutinaId], $idsMantener));
        } elseif (!empty($ejercicios)) {
            $db->prepare("DELETE FROM ejercicios WHERE rutina_id = ?")->execute([$rutinaId]);
        }

        $stUp = $db->prepare("
            UPDATE ejercicios
            SET nombre = ?, series = ?, repeticiones = ?, rir = ?, orden = ?,
                tipo_implemento = ?, unidad_carga = ?, descripcion = ?
            WHERE id = ? AND rutina_id = ?
        ");
        $stIns = $db->prepare("
            INSERT INTO ejercicios
            (rutina_id, nombre, series, repeticiones, rir, orden, tipo_implemento, unidad_carga, descripcion, grupo_muscular, equipamiento, imagen_path, imagen_demo_path)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $orden = 1;
        foreach ($ejercicios as $ej) {
            $eid    = (int)($ej['id'] ?? 0);
            $nombre = trim((string)($ej['nombre'] ?? ''));
            if ($nombre === '') continue;

            $seriesRaw = $ej['series_detalle'] ?? $ej['series'] ?? [];
            if (!is_array($seriesRaw)) $seriesRaw = [];
            $series = max(1, min(30, count($seriesRaw) > 0 ? count($seriesRaw) : (int)($ej['series'] ?? 3)));
            $reps   = trim((string)($ej['repeticiones'] ?? '10'));
            if ($reps === '') $reps = '10';

            $rirVal = isset($ej['rir']) && $ej['rir'] !== '' && $ej['rir'] !== null ? max(0, min(5, (int)$ej['rir'])) : null;
            $imp    = trim((string)($ej['tipo_implemento'] ?? 'barra'));
            if (!in_array($imp, ['mancuerna', 'barra', 'polea_maquina', 'sin_peso'], true)) {
                $imp = 'barra';
            }
            $unidad = trim((string)($ej['unidad_carga'] ?? 'kg'));
            if (!in_array($unidad, ['kg', 'lbs', 'placas', 'ninguna'], true)) {
                $unidad = ($imp === 'sin_peso') ? 'ninguna' : 'kg';
            }

            $descanso = isset($ej['descanso']) && is_numeric($ej['descanso']) ? (int)$ej['descanso'] : null;
            $stInsPlEj->execute([$plantillaId, $nombre, $orden, $descanso, $imp, $unidad]);
            $plEjId = (int)$db->lastInsertId();

            $pesosValores = [];
            if (!empty($seriesRaw)) {
                foreach ($seriesRaw as $sIdx => $sData) {
                    $sNum  = (int)($sData['numero_serie'] ?? ($sData['numero'] ?? ($sIdx + 1)));
                    $sTipo = strtolower(trim((string)($sData['tipo_serie'] ?? ($sData['tipo'] ?? 'normal'))));
                    if (!in_array($sTipo, ['normal', 'calentamiento', 'fallo', 'drop'], true)) {
                        $sTipo = 'normal';
                    }
                    $sKg   = isset($sData['peso']) && $sData['peso'] !== '' && $sData['peso'] !== null && is_numeric($sData['peso']) ? (float)$sData['peso'] : null;
                    if ($imp === 'sin_peso') $sKg = null;
                    $sRep  = isset($sData['reps']) && is_numeric($sData['reps']) ? (int)$sData['reps'] : (int)$reps;
                    $sRir  = isset($sData['rir']) && $sData['rir'] !== '' && $sData['rir'] !== null && is_numeric($sData['rir']) ? max(0, min(5, (int)$sData['rir'])) : $rirVal;
                    $stInsPlS->execute([$plEjId, $sNum, $sTipo, $sKg, $sRep, $sRir]);
                    if ($sKg !== null) {
                        $pesosValores[] = $sKg;
                    }
                }
            } else {
                $pesoVal = isset($ej['peso']) && $ej['peso'] !== '' && $ej['peso'] !== null ? (float)$ej['peso'] : null;
                if ($imp === 'sin_peso') $pesoVal = null;
                for ($s = 1; $s <= $series; $s++) {
                    $stInsPlS->execute([$plEjId, $s, 'normal', $pesoVal, (int)$reps, $rirVal]);
                    if ($pesoVal !== null) {
                        $pesosValores[] = $pesoVal;
                    }
                }
            }

            if ($imp === 'sin_peso' || $unidad === 'ninguna') {
                $desc = 'Sin peso';
            } elseif (!empty($pesosValores)) {
                $unicos = array_unique($pesosValores);
                $uniSuffix = ($unidad === 'placas') ? 'placas' : ($unidad === 'lbs' ? 'lbs' : 'kg');
                $desc = count($unicos) === 1 ? "{$pesosValores[0]} {$uniSuffix}" : (implode('-', $pesosValores) . " {$uniSuffix}");
            } else {
                $desc = !empty($ej['descripcion']) ? trim((string)$ej['descripcion']) : '';
            }

            if ($eid > 0) {
                $stUp->execute([$nombre, $series, $reps, $rirVal, $orden, $imp, $unidad, $desc, $eid, $rutinaId]);
            } else {
                $cat = CatalogoService::obtenerMetaPorNombre($db, $nombre, null);

                $grupo       = $cat['grupo_muscular'] ?? inferirGrupoMuscularProgreso($nombre);
                $equip       = $cat['equipamiento'] ?? inferirEquipamientoProgreso($nombre, $imp);
                $imgPath     = $cat['imagen_path'] ?? null;
                $imgDemoPath = $cat['imagen_demo_path'] ?? null;

                if (!$cat) {
                    asegurarEjercicioEnCatalogo($db, $nombre, $grupo, $imp, $unidad, $equip, null);
                }

                $stIns->execute([
                    $rutinaId, $nombre, $series, $reps, $rirVal, $orden,
                    $imp, $unidad, $desc, $grupo, $equip, $imgPath, $imgDemoPath
                ]);
            }
            $orden++;
        }

        $db->commit();
        jsonOut(true, 'Rutina actualizada correctamente.', [
            'dias_programados' => adminObtenerDiasProgramados($db, $adminClienteId),
        ]);
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiActualizarRutinaCompleta] ' . $e->getMessage());
        jsonOut(false, 'Error interno al actualizar la rutina.', [], 500);
    }
}

function adminApiEliminarRutina(): void {
    csrfCheck();
    header('Content-Type: application/json; charset=utf-8');

    $db = getDB();
    $adminClienteId = adminObtenerClienteIdPropio($db);
    $data = array_merge($_POST, getJsonPayload());
    $rutinaId = (int)($data['rutina_id'] ?? 0);

    if ($rutinaId <= 0) {
        jsonOut(false, 'ID de rutina inválido.');
    }

    $chk = $db->prepare("SELECT id FROM rutinas WHERE id = ? AND cliente_id = ? LIMIT 1");
    $chk->execute([$rutinaId, $adminClienteId]);
    if (!$chk->fetch()) {
        jsonOut(false, 'Rutina no encontrada.');
    }

    try {
        $db->beginTransaction();
        $db->prepare("DELETE FROM rutinas WHERE id = ?")->execute([$rutinaId]);
        $db->commit();
        jsonOut(true, 'Rutina eliminada correctamente.', [
            'dias_programados' => adminObtenerDiasProgramados($db, $adminClienteId),
        ]);
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiEliminarRutina] ' . $e->getMessage());
        jsonOut(false, 'Error interno al eliminar la rutina.', [], 500);
    }
}

function adminApiGuardarSerie(): void {
    csrfCheck();
    header('Content-Type: application/json; charset=utf-8');

    try {
        $db = getDB();
        $adminUserId    = (int)($_SESSION['user_id'] ?? 0);
        $adminClienteId = adminObtenerClienteIdPropio($db);
        $in = array_merge($_POST, getJsonPayload());

        $ejercicioId = (int)($in['ejercicio_id'] ?? 0);
        $numSerie    = (int)($in['numero_serie'] ?? 0);
        $rawReps     = trim((string)($in['reps'] ?? ''));
        $rawPeso     = trim((string)($in['peso'] ?? ''));
        $rawUnidad   = strtolower(trim((string)($in['unidad_carga'] ?? '')));
        $rawTipoImp  = strtolower(trim((string)($in['tipo_implemento'] ?? '')));
        $tipoSerie   = strtolower(trim((string)($in['tipo_serie'] ?? 'normal')));
        if (!in_array($tipoSerie, ['normal', 'calentamiento', 'fallo', 'drop'], true)) {
            $tipoSerie = 'normal';
        }

        $fechaEntrenamiento = trim((string)($in['fecha'] ?? date('Y-m-d')));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaEntrenamiento)) {
            $fechaEntrenamiento = date('Y-m-d');
        }

        if ($ejercicioId < 1 || $numSerie < 1 || $numSerie > 30) {
            jsonOut(false, 'Número de serie inválido.', [], 422);
        }

        $stEjInfo = $db->prepare("
            SELECT e.nombre, e.rutina_id, e.tipo_implemento, e.unidad_carga
            FROM ejercicios e
            JOIN rutinas r ON r.id = e.rutina_id
            WHERE e.id = ? AND r.cliente_id = ?
            LIMIT 1
        ");
        $stEjInfo->execute([$ejercicioId, $adminClienteId]);
        $ejInfo = $stEjInfo->fetch(PDO::FETCH_ASSOC);
        if (!$ejInfo) {
            jsonOut(false, 'Ejercicio no encontrado en tu rutina.', [], 404);
        }
        $rutinaId = (int)$ejInfo['rutina_id'];

        $accion = strtolower(trim((string)($in['accion'] ?? 'guardar')));
        $esDesmarcar = ($accion === 'desmarcar') || (isset($in['completado']) && (int)$in['completado'] === 0);

        if ($esDesmarcar) {
            $db->beginTransaction();
            $db->prepare("DELETE FROM sesion_series WHERE ejercicio_id = ? AND cliente_id = ? AND fecha = ? AND numero_serie = ?")
               ->execute([$ejercicioId, $adminClienteId, $fechaEntrenamiento, $numSerie]);

            $stCheck = $db->prepare("SELECT COUNT(*) FROM sesion_series WHERE ejercicio_id = ? AND cliente_id = ? AND fecha = ? AND completado = 1");
            $stCheck->execute([$ejercicioId, $adminClienteId, $fechaEntrenamiento]);
            $quedan = (int)$stCheck->fetchColumn();

            if ($quedan === 0) {
                $db->prepare("DELETE FROM sesiones WHERE ejercicio_id = ? AND cliente_id = ? AND fecha = ?")
                   ->execute([$ejercicioId, $adminClienteId, $fechaEntrenamiento]);
            } else {
                $db->prepare("UPDATE sesiones SET reps_realizadas = (SELECT MAX(reps_realizadas) FROM sesion_series WHERE ejercicio_id=? AND cliente_id=? AND fecha=?), peso_usado = (SELECT MAX(peso_usado) FROM sesion_series WHERE ejercicio_id=? AND cliente_id=? AND fecha=?) WHERE ejercicio_id = ? AND cliente_id = ? AND fecha = ?")
                   ->execute([$ejercicioId, $adminClienteId, $fechaEntrenamiento, $ejercicioId, $adminClienteId, $fechaEntrenamiento, $ejercicioId, $adminClienteId, $fechaEntrenamiento]);
            }

            $stTotalRut = $db->prepare("SELECT COUNT(*) FROM sesion_series ss JOIN ejercicios e ON ss.ejercicio_id = e.id WHERE ss.cliente_id = ? AND e.rutina_id = ? AND ss.fecha = ? AND ss.completado = 1");
            $stTotalRut->execute([$adminClienteId, $rutinaId, $fechaEntrenamiento]);
            $quedanRutina = (int)$stTotalRut->fetchColumn();

            if ($quedanRutina === 0) {
                $db->prepare("DELETE FROM sesiones_asistencia WHERE cliente_id = ? AND rutina_id = ? AND (DATE(inicio_en) = ? OR DATE(fin_en) = ?)")
                   ->execute([$adminClienteId, $rutinaId, $fechaEntrenamiento, $fechaEntrenamiento]);
            } else {
                $db->prepare("UPDATE sesiones_asistencia SET estado = 'iniciada', fin_en = NULL WHERE cliente_id = ? AND rutina_id = ? AND (DATE(inicio_en) = ? OR DATE(fin_en) = ?)")
                   ->execute([$adminClienteId, $rutinaId, $fechaEntrenamiento, $fechaEntrenamiento]);
            }

            $db->commit();
            jsonOut(true, 'Serie desmarcada.', ['desmarcado' => true, 'rutina_completada' => false]);
        }

        $tipoImplemento = in_array($rawTipoImp, ['mancuerna', 'barra', 'polea_maquina', 'sin_peso'], true)
            ? $rawTipoImp
            : ($ejInfo['tipo_implemento'] ?? 'barra');

        $unidadCarga = in_array($rawUnidad, ['kg', 'lbs', 'placas', 'ninguna'], true)
            ? $rawUnidad
            : ($ejInfo['unidad_carga'] ?? 'kg');

        if ($tipoImplemento === 'sin_peso') {
            $unidadCarga = 'ninguna';
        }

        $reps = $rawReps !== '' ? max(1, min(100, (int)$rawReps)) : 10;
        $pesoUsado   = 0.00;
        $pesoPorLado = null;
        $kgReal      = 0.00;

        if ($tipoImplemento !== 'sin_peso' && $unidadCarga !== 'ninguna' && $rawPeso !== '') {
            $valNumeric = max(0.0, round((float)str_replace(',', '.', $rawPeso), 2));
            if ($tipoImplemento === 'mancuerna') {
                $maxM        = ($unidadCarga === 'lbs') ? 450 : 200;
                $pesoUsado   = min($maxM, $valNumeric);
                $pesoPorLado = $pesoUsado;
                $kgReal      = ($unidadCarga === 'lbs') ? round(($pesoUsado * 2) / 2.20462, 2) : round($pesoUsado * 2, 2);
            } elseif ($tipoImplemento === 'polea_maquina') {
                $maxP      = ($unidadCarga === 'placas') ? 40 : (($unidadCarga === 'lbs') ? 1000 : 500);
                $pesoUsado = min($maxP, $valNumeric);
                $kgReal    = ($unidadCarga === 'lbs') ? round($pesoUsado / 2.20462, 2) : $pesoUsado;
            } else {
                $maxB      = ($unidadCarga === 'lbs') ? 1100 : 500;
                $pesoUsado = min($maxB, $valNumeric);
                $kgReal    = ($unidadCarga === 'lbs') ? round($pesoUsado / 2.20462, 2) : $pesoUsado;
            }
        }

        $db->beginTransaction();

        $stUpsert = $db->prepare("
            INSERT INTO sesion_series
                (cliente_id, ejercicio_id, rutina_id, ejercicio_nombre, numero_serie, reps_realizadas, peso_usado, completado, fecha, tipo_serie, tipo_implemento, unidad_carga, peso_por_lado, kg_real)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                reps_realizadas = VALUES(reps_realizadas),
                peso_usado      = VALUES(peso_usado),
                completado      = 1,
                tipo_serie      = VALUES(tipo_serie),
                tipo_implemento = VALUES(tipo_implemento),
                unidad_carga    = VALUES(unidad_carga),
                peso_por_lado   = VALUES(peso_por_lado),
                kg_real         = VALUES(kg_real)
        ");
        $stUpsert->execute([
            $adminClienteId, $ejercicioId, $rutinaId, $ejInfo['nombre'],
            $numSerie, $reps, $pesoUsado, $fechaEntrenamiento,
            $tipoSerie, $tipoImplemento, $unidadCarga, $pesoPorLado, $kgReal
        ]);

        $stSesion = $db->prepare("
            INSERT INTO sesiones (ejercicio_id, cliente_id, reps_realizadas, peso_usado, completado, fecha)
            VALUES (?, ?, ?, ?, 1, ?)
            ON DUPLICATE KEY UPDATE
                reps_realizadas = VALUES(reps_realizadas),
                peso_usado      = VALUES(peso_usado),
                completado      = 1
        ");
        $stSesion->execute([$ejercicioId, $adminClienteId, $reps, $pesoUsado, $fechaEntrenamiento]);

        $stPlan = $db->prepare("SELECT COALESCE(SUM(GREATEST(1, COALESCE(series, 1))), 0) FROM ejercicios WHERE rutina_id = ?");
        $stPlan->execute([$rutinaId]);
        $seriesPlanificadas = (int)$stPlan->fetchColumn();

        $stComp = $db->prepare("SELECT COUNT(*) FROM sesion_series ss JOIN ejercicios e ON ss.ejercicio_id = e.id WHERE ss.cliente_id = ? AND e.rutina_id = ? AND ss.fecha = ? AND ss.completado = 1");
        $stComp->execute([$adminClienteId, $rutinaId, $fechaEntrenamiento]);
        $seriesCompletadas = (int)$stComp->fetchColumn();

        $rutinaCompletada = ($seriesPlanificadas > 0 && $seriesCompletadas >= $seriesPlanificadas);
        $nuevoEstadoSesion = $rutinaCompletada ? 'completada' : 'iniciada';

        $stAsisChk = $db->prepare("SELECT id FROM sesiones_asistencia WHERE cliente_id = ? AND rutina_id = ? AND (DATE(inicio_en) = ? OR DATE(fin_en) = ?) LIMIT 1");
        $stAsisChk->execute([$adminClienteId, $rutinaId, $fechaEntrenamiento, $fechaEntrenamiento]);
        $asisRow = $stAsisChk->fetch(PDO::FETCH_ASSOC);

        if (!$asisRow) {
            $fechaTs = ($fechaEntrenamiento === date('Y-m-d')) ? date('Y-m-d H:i:s') : ($fechaEntrenamiento . ' 12:00:00');
            $finTs   = $rutinaCompletada ? $fechaTs : null;
            $db->prepare("INSERT INTO sesiones_asistencia (rutina_id, cliente_id, entrenador_id, inicio_en, fin_en, estado, verificada) VALUES (?, ?, ?, ?, ?, ?, 1)")
               ->execute([$rutinaId, $adminClienteId, $adminUserId, $fechaTs, $finTs, $nuevoEstadoSesion]);
        } else {
            if ($rutinaCompletada) {
                $db->prepare("UPDATE sesiones_asistencia SET estado = 'completada', fin_en = COALESCE(fin_en, NOW()), verificada = 1 WHERE id = ?")
                   ->execute([(int)$asisRow['id']]);
            } else {
                $db->prepare("UPDATE sesiones_asistencia SET verificada = 1 WHERE id = ?")
                   ->execute([(int)$asisRow['id']]);
            }
        }

        $db->commit();
        jsonOut(true, $rutinaCompletada ? '¡Rutina del día completada!' : 'Serie registrada.', [
            'completado'        => true,
            'rutina_completada' => $rutinaCompletada,
            'ejercicio_id'      => $ejercicioId,
            'numero_serie'      => $numSerie,
            'reps'              => $reps,
            'peso'              => $pesoUsado,
        ]);
    } catch (\Throwable $e) {
        if (isset($db) && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiGuardarSerie] ' . $e->getMessage());
        jsonOut(false, 'Error interno al guardar la serie.', [], 500);
    }
}

function adminApiGuardarRutinaComoPlantilla(): void {
    csrfCheck();
    header('Content-Type: application/json; charset=utf-8');

    $db = getDB();
    $adminUserId    = (int)($_SESSION['user_id'] ?? 0);
    $adminClienteId = adminObtenerClienteIdPropio($db);
    $data = array_merge($_POST, getJsonPayload());

    $rutinaId = (int)($data['rutina_id'] ?? 0);
    $titulo   = trim(sanitizeInput((string)($data['titulo'] ?? ''), 120));

    if ($rutinaId <= 0) {
        jsonOut(false, 'ID de rutina inválido.');
    }

    $stRut = $db->prepare("SELECT * FROM rutinas WHERE id = ? AND cliente_id = ? LIMIT 1");
    $stRut->execute([$rutinaId, $adminClienteId]);
    $rut = $stRut->fetch(PDO::FETCH_ASSOC);
    if (!$rut) {
        jsonOut(false, 'Rutina no encontrada.');
    }

    if ($titulo === '') {
        $titulo = trim((string)($rut['titulo'] ?: ($rut['descripcion'] ?: 'Plantilla Superadmin')));
    }

    try {
        $db->beginTransaction();

        $stPl = $db->prepare("
            INSERT INTO plantillas_rutinas (entrenador_id, titulo, descripcion, es_catalogo, cardio_activo, cardio_tiempo, cardio_indicaciones)
            VALUES (?, ?, ?, 1, ?, ?, ?)
        ");
        $stPl->execute([
            $adminUserId,
            $titulo,
            $rut['descripcion'] ?? $titulo,
            (int)($rut['cardio_activo'] ?? 0),
            $rut['cardio_tiempo'] ?? null,
            $rut['cardio_indicaciones'] ?? null,
        ]);
        $newPlId = (int)$db->lastInsertId();

        $stEjs = $db->prepare("SELECT * FROM ejercicios WHERE rutina_id = ? ORDER BY orden ASC, id ASC");
        $stEjs->execute([$rutinaId]);
        $ejs = $stEjs->fetchAll(PDO::FETCH_ASSOC);

        $stInsPlEj = $db->prepare("
            INSERT INTO plantilla_ejercicios (plantilla_id, nombre_override, orden, descanso_segundos, tipo_implemento, unidad_carga)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stInsPlS = $db->prepare("
            INSERT INTO plantilla_series (plantilla_ej_id, numero_serie, tipo_serie, kg_objetivo, reps_objetivo, rir)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        // Precargar series y descansos reales desde la plantilla vinculada de la rutina (si existe)
        $origPlId = (int)($rut['plantilla_id'] ?? 0);
        $origSeriesPorOrden = [];
        $origDescansoPorOrden = [];
        if ($origPlId > 0) {
            $stOrigS = $db->prepare("
                SELECT pe.orden, pe.descanso_segundos, ps.numero_serie, ps.tipo_serie, ps.kg_objetivo, ps.reps_objetivo, ps.rir
                FROM plantilla_ejercicios pe
                LEFT JOIN plantilla_series ps ON ps.plantilla_ej_id = pe.id
                WHERE pe.plantilla_id = ?
                ORDER BY pe.orden ASC, ps.numero_serie ASC
            ");
            $stOrigS->execute([$origPlId]);
            foreach ($stOrigS->fetchAll(PDO::FETCH_ASSOC) as $os) {
                $ord = (int)$os['orden'];
                if (!array_key_exists($ord, $origDescansoPorOrden) && $os['descanso_segundos'] !== null) {
                    $origDescansoPorOrden[$ord] = (int)$os['descanso_segundos'];
                }
                if ($os['numero_serie'] !== null) {
                    $origSeriesPorOrden[$ord][] = $os;
                }
            }
        }

        foreach ($ejs as $idx => $ej) {
            $ordEj = (int)($ej['orden'] ?? ($idx + 1));
            $descEj = $origDescansoPorOrden[$ordEj] ?? null;
            $stInsPlEj->execute([
                $newPlId,
                $ej['nombre'],
                $idx + 1,
                $descEj,
                $ej['tipo_implemento'] ?? 'barra',
                $ej['unidad_carga'] ?? 'kg',
            ]);
            $plEjId = (int)$db->lastInsertId();

            if (!empty($origSeriesPorOrden[$ordEj])) {
                foreach ($origSeriesPorOrden[$ordEj] as $os) {
                    $stInsPlS->execute([
                        $plEjId,
                        (int)$os['numero_serie'],
                        in_array((string)($os['tipo_serie'] ?? 'normal'), ['normal', 'calentamiento', 'fallo', 'drop'], true) ? $os['tipo_serie'] : 'normal',
                        $os['kg_objetivo'] !== null ? (float)$os['kg_objetivo'] : null,
                        $os['reps_objetivo'] !== null ? (int)$os['reps_objetivo'] : null,
                        $os['rir'] !== null ? (int)$os['rir'] : null,
                    ]);
                }
            } else {
                $numS = max(1, (int)($ej['series'] ?? 3));
                $reps = (int)($ej['repeticiones'] ?? 10) ?: 10;
                $rir  = isset($ej['rir']) && is_numeric($ej['rir']) ? (int)$ej['rir'] : 2;
                for ($s = 1; $s <= $numS; $s++) {
                    $stInsPlS->execute([$plEjId, $s, 'normal', null, $reps, $rir]);
                }
            }
        }

        $db->commit();
        jsonOut(true, "Rutina guardada como plantilla \"{$titulo}\".", ['plantilla_id' => $newPlId]);
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiGuardarRutinaComoPlantilla] ' . $e->getMessage());
        jsonOut(false, 'Error interno al guardar la plantilla.', [], 500);
    }
}

function adminApiListarPlantillasCatalogo(): void {
    header('Content-Type: application/json; charset=utf-8');
    $db = getDB();
    $st = $db->query("
        SELECT p.id, p.titulo, p.descripcion, p.cardio_activo, p.cardio_tiempo, p.cardio_indicaciones,
               COUNT(pe.id) AS total_ejercicios
        FROM plantillas_rutinas p
        LEFT JOIN plantilla_ejercicios pe ON pe.plantilla_id = p.id
        WHERE p.es_catalogo = 1
        GROUP BY p.id
        ORDER BY p.id DESC
    ");
    $plantillas = $st ? $st->fetchAll(PDO::FETCH_ASSOC) : [];

    // Zero N+1: cargar ejercicios y series en 2 consultas por lotes
    $plIds = array_values(array_unique(array_filter(array_map(static fn($r) => (int)$r['id'], $plantillas))));
    $ejsPorPl = [];
    if (!empty($plIds)) {
        $phPl = implode(',', array_fill(0, count($plIds), '?'));
        $stEj = $db->prepare("
            SELECT pe.id, pe.plantilla_id, pe.nombre_override AS nombre, pe.orden, pe.descanso_segundos, pe.tipo_implemento, pe.unidad_carga
            FROM plantilla_ejercicios pe
            WHERE pe.plantilla_id IN ($phPl)
            ORDER BY pe.plantilla_id DESC, pe.orden ASC, pe.id ASC
        ");
        $stEj->execute($plIds);
        $todosEjs = $stEj->fetchAll(PDO::FETCH_ASSOC);

        $ejIds = array_values(array_unique(array_filter(array_map(static fn($r) => (int)$r['id'], $todosEjs))));
        $seriesPorEj = [];
        if (!empty($ejIds)) {
            $phEj = implode(',', array_fill(0, count($ejIds), '?'));
            $stS = $db->prepare("
                SELECT plantilla_ej_id, numero_serie, tipo_serie, kg_objetivo, reps_objetivo, rir
                FROM plantilla_series
                WHERE plantilla_ej_id IN ($phEj)
                ORDER BY plantilla_ej_id ASC, numero_serie ASC
            ");
            $stS->execute($ejIds);
            foreach ($stS->fetchAll(PDO::FETCH_ASSOC) as $sRow) {
                $ejKey = (int)$sRow['plantilla_ej_id'];
                unset($sRow['plantilla_ej_id']);
                $seriesPorEj[$ejKey][] = $sRow;
            }
        }

        foreach ($todosEjs as $ex) {
            $pid = (int)$ex['plantilla_id'];
            $exId = (int)$ex['id'];
            unset($ex['plantilla_id']);
            $ex['series'] = $seriesPorEj[$exId] ?? [];
            $ejsPorPl[$pid][] = $ex;
        }
    }

    foreach ($plantillas as &$pl) {
        $pl['ejercicios'] = $ejsPorPl[(int)$pl['id']] ?? [];
    }
    unset($pl);
    echo json_encode(['ok' => true, 'plantillas' => $plantillas], JSON_UNESCAPED_UNICODE);
    exit;
}

// ══════════════════════════════════════════════════════════════════
//  MÓDULO 2: GESTIÓN DE ENTRENADORES Y ATLETAS ASIGNADOS
// ══════════════════════════════════════════════════════════════════

function adminGestion(): void {
    $db = getDB();

    // Obtener entrenadores activos (evita crear membresías a solicitudes pendientes o rechazadas)
    $stEnt = $db->query("
        SELECT u.id, u.nombre, u.email, u.activo, u.estado_cuenta, u.creado_en, u.avatar_url
        FROM usuarios u
        WHERE u.rol = 'entrenador'
          AND u.estado_cuenta = 'activo'
        ORDER BY u.id ASC
    ");
    $rowsEnt = $stEnt->fetchAll(PDO::FETCH_ASSOC);

    // Solicitudes de nuevos entrenadores pendientes de aprobación por el Super Admin
    $stSolEnt = $db->query("
        SELECT u.id AS usuario_id, u.nombre, u.email, u.creado_en, u.avatar_url, u.auth_provider
        FROM usuarios u
        WHERE u.rol = 'entrenador'
          AND u.estado_cuenta = 'pendiente'
        ORDER BY u.creado_en DESC, u.id DESC
    ");
    $solicitudesEntrenadores = $stSolEnt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todos los atletas reales (rol = 'cliente') y agruparlos por entrenador_id
    $stAtletas = $db->query("
        SELECT c.id AS cliente_id, c.usuario_id, c.cedula, c.entrenador_id, c.peso_kg, c.altura_cm, c.objetivo,
               u.nombre, u.email, u.estado_cuenta, u.avatar_url,
               ue.nombre AS entrenador_nombre
        FROM clientes c
        JOIN usuarios u ON u.id = c.usuario_id
        LEFT JOIN usuarios ue ON ue.id = c.entrenador_id
        WHERE u.rol = 'cliente'
        ORDER BY u.nombre ASC
    ");
    $atletas = $stAtletas->fetchAll(PDO::FETCH_ASSOC);

    $atletasPorEntrenador = [];
    foreach ($atletas as $atl) {
        $eid = (int)($atl['entrenador_id'] ?? 0);
        $atletasPorEntrenador[$eid][] = $atl;
    }

    $entrenadores   = [];
    $kpiActivos     = 0;
    $kpiPorVencer   = 0;
    $kpiEnGracia    = 0;
    $kpiSuspendidos = 0;

    foreach ($rowsEnt as $r) {
        $eid = (int)$r['id'];
        $mem = obtenerEstadoMembresiaEntrenador($eid);
        $listaAtl = $atletasPorEntrenador[$eid] ?? [];

        $activosCount = 0;
        $pendCount    = 0;
        foreach ($listaAtl as $aItem) {
            if (($aItem['estado_cuenta'] ?? '') === 'activo') {
                $activosCount++;
            } elseif (($aItem['estado_cuenta'] ?? '') === 'pendiente') {
                $pendCount++;
            }
        }

        $entrenadores[] = array_merge($r, [
            'membresia'          => $mem,
            'total_atletas'      => count($listaAtl),
            'atletas_activos'    => $activosCount,
            'atletas_pendientes' => $pendCount,
            'atletas_lista'      => $listaAtl,
        ]);

        $est = $mem['estado_calculado'] ?? 'activa';
        if ($est === 'suspendida' || $est === 'pausada_manual') {
            $kpiSuspendidos++;
        } elseif ($est === 'en_gracia' || $est === 'gracia') {
            $kpiEnGracia++;
        } elseif ($est === 'recordatorio') {
            $kpiPorVencer++;
        } else {
            $kpiActivos++;
        }
    }

    $pageTitle = 'Gestión de Entrenadores y Atletas';
    require BASE_DIR . '/views/admin/gestion.php';
}

// ══════════════════════════════════════════════════════════════════
//  MÓDULO 3: CONFIGURACIÓN (MENSAJE DE AVISO AL PAUSAR Y SISTEMA)
// ══════════════════════════════════════════════════════════════════

function adminConfiguracion(): void {
    $db = getDB();

    // Obtener todos los entrenadores activos con su membresía y mensaje de aviso
    $stEnt = $db->query("
        SELECT u.id, u.nombre, u.email, u.avatar_url
        FROM usuarios u
        WHERE u.rol = 'entrenador'
          AND u.estado_cuenta = 'activo'
        ORDER BY u.id ASC
    ");
    $rowsEnt = $stEnt->fetchAll(PDO::FETCH_ASSOC);
    $entrenadores = [];
    foreach ($rowsEnt as $r) {
        $eid = (int)$r['id'];
        $r['membresia'] = obtenerEstadoMembresiaEntrenador($eid);
        $entrenadores[] = $r;
    }

    $mensajePausaGlobal = 'La web ha sido pausada hasta que logres pagar la membresía.';
    $mensajeAvisoGlobal = 'Recordatorio: Se acerca tu fecha de pago de la membresía mensual. Recuerda realizar tu abono a tiempo.';
    try {
        $stAj = $db->query("SELECT clave, valor FROM ajustes_globales WHERE clave IN ('mensaje_pausa_entrenadores', 'mensaje_aviso_entrenadores')");
        if ($stAj) {
            foreach ($stAj->fetchAll(PDO::FETCH_ASSOC) as $rowAj) {
                if ($rowAj['clave'] === 'mensaje_pausa_entrenadores' && trim((string)$rowAj['valor']) !== '') {
                    $mensajePausaGlobal = trim((string)$rowAj['valor']);
                }
                if ($rowAj['clave'] === 'mensaje_aviso_entrenadores' && trim((string)$rowAj['valor']) !== '') {
                    $mensajeAvisoGlobal = trim((string)$rowAj['valor']);
                }
            }
        }
    } catch (\Throwable $e) {}

    $adminUserId      = (int)($_SESSION['user_id'] ?? 0);
    $temaInfoAdmin    = xpResolverTemaWeb($db, $adminUserId);
    $temaActual       = $temaInfoAdmin['tema'];
    $temaGlobalActivo = !empty($temaInfoAdmin['global_activo']);

    // Catálogo global de ejercicios (visible para todos los entrenadores)
    $catalogoPorGrupo = CatalogoService::obtenerCatalogoAgrupado($db, null);

    // Lista de atletas para el Importador IA (incluye perfil personal del Super Admin + atletas activos)
    adminObtenerClienteIdPropio($db);
    $atletasList = [];
    try {
        $stAtl = $db->prepare("
            SELECT c.id AS cliente_id, u.nombre
            FROM clientes c
            JOIN usuarios u ON u.id = c.usuario_id
            WHERE u.activo = 1
            ORDER BY (u.id = ?) DESC, u.nombre ASC
        ");
        $stAtl->execute([$adminUserId]);
        $atletasList = $stAtl->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Throwable $e) {}

    // Métricas para Copia de Seguridad
    try {
        $dbName      = (string)$db->query("SELECT DATABASE()")->fetchColumn();
        $totalTablas = (int)$db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()")->fetchColumn();
        $totalSeries = (int)$db->query("SELECT COUNT(*) FROM sesion_series")->fetchColumn();
    } catch (\Throwable $e) {
        $dbName      = 'xprod_fit';
        $totalTablas = 0;
        $totalSeries = 0;
    }

    $pageTitle = 'Configuración — Superadmin';
    require BASE_DIR . '/views/admin/configuracion.php';
}

function adminApiGuardarConfiguracion(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());

    $tipo        = trim((string)($in['tipo'] ?? 'mensaje_pausa'));
    $adminUserId = (int)($_SESSION['user_id'] ?? 0);
    $temasOk     = ['classic', 'obsidian', 'royal-plum', 'deep-ocean', 'nordic-frost', 'dark', 'light'];

    try {
        $upsertAj = $db->prepare("
            INSERT INTO ajustes_globales (clave, valor) VALUES (?, ?)
            ON DUPLICATE KEY UPDATE valor = VALUES(valor)
        ");

        if ($tipo === 'tema_alcance') {
            $alcanceTema = (($in['alcance'] ?? '') === 'global' || !empty($in['global'])) ? 'global' : 'personal';
            $upsertAj->execute(['tema_superadmin_alcance', $alcanceTema]);

            $curVer = (int)($db->query("SELECT valor FROM ajustes_globales WHERE clave = 'tema_global_ver' LIMIT 1")->fetchColumn() ?: 1);
            $temaAplicar = trim((string)($in['tema'] ?? ''));

            if ($alcanceTema === 'global' && in_array($temaAplicar, $temasOk, true)) {
                $newVer = max($curVer + 1, time());
                $upsertAj->execute(['tema_sistema', $temaAplicar]);
                $upsertAj->execute(['tema_global_ver', (string)$newVer]);
                if ($adminUserId > 0) {
                    $upsertAj->execute(['tema_usuario_' . $adminUserId, $temaAplicar . '|' . $newVer]);
                }
                jsonOut(true, 'Modo Global activado y paleta aplicada a toda la web.', [
                    'alcance'       => 'global',
                    'global_activo' => true,
                    'tema'          => $temaAplicar,
                    'global_ver'    => $newVer,
                ]);
            }

            jsonOut(true, $alcanceTema === 'global'
                ? 'Modo Global activado: los cambios de paleta se aplicarán a toda la web.'
                : 'Modo Solo Super Admin activado: los cambios de paleta solo afectarán tu vista.', [
                'alcance'       => $alcanceTema,
                'global_activo' => ($alcanceTema === 'global'),
                'global_ver'    => $curVer,
            ]);
        }

        if ($tipo === 'tema_sistema') {
            $tema = trim((string)($in['tema'] ?? 'classic'));
            if (!in_array($tema, $temasOk, true)) {
                jsonOut(false, 'Tema inválido.');
            }

            $alcanceReq = trim((string)($in['alcance'] ?? ''));
            if ($alcanceReq === 'global' || $alcanceReq === 'personal') {
                $upsertAj->execute(['tema_superadmin_alcance', $alcanceReq]);
                $esGlobal = ($alcanceReq === 'global');
            } else {
                $dbAlcance = (string)($db->query("SELECT valor FROM ajustes_globales WHERE clave = 'tema_superadmin_alcance' LIMIT 1")->fetchColumn() ?: 'personal');
                $esGlobal = ($dbAlcance === 'global');
            }

            $curVer = (int)($db->query("SELECT valor FROM ajustes_globales WHERE clave = 'tema_global_ver' LIMIT 1")->fetchColumn() ?: 1);

            if ($esGlobal) {
                $newVer = max($curVer + 1, time());
                $upsertAj->execute(['tema_sistema', $tema]);
                $upsertAj->execute(['tema_global_ver', (string)$newVer]);
                if ($adminUserId > 0) {
                    $upsertAj->execute(['tema_usuario_' . $adminUserId, $tema . '|' . $newVer]);
                }
                jsonOut(true, 'Paleta aplicada de forma global a toda la web.', [
                    'tema'          => $tema,
                    'alcance'       => 'global',
                    'global_activo' => true,
                    'global_ver'    => $newVer,
                ]);
            }

            if ($adminUserId > 0) {
                $upsertAj->execute(['tema_usuario_' . $adminUserId, $tema . '|' . $curVer]);
            }
            jsonOut(true, 'Paleta aplicada solo para tu sesión de Super Admin.', [
                'tema'          => $tema,
                'alcance'       => 'personal',
                'global_activo' => false,
                'global_ver'    => $curVer,
            ]);
        }

        // Guardar mensaje (aviso o suspensión/pausa) global o por entrenador
        $alcance = trim((string)($in['alcance'] ?? 'todos')); // 'todos' o ID de entrenador
        $mensaje = trim(sanitizeInput((string)($in['mensaje'] ?? $in['mensaje_bloqueo'] ?? ''), 255));

        $esAviso = ($tipo === 'mensaje_aviso' || $tipo === 'aviso');
        $cfgTipo = $esAviso ? [
            'claveGlobal' => 'mensaje_aviso_entrenadores',
            'columna'     => 'mensaje_aviso',
            'defaultMsg'  => 'Recordatorio: Se acerca tu fecha de pago de la membresía mensual. Recuerda realizar tu abono a tiempo.',
            'tipoOut'     => 'aviso',
            'etiqueta'    => 'Mensaje de aviso',
        ] : [
            'claveGlobal' => 'mensaje_pausa_entrenadores',
            'columna'     => 'mensaje_bloqueo',
            'defaultMsg'  => 'La web ha sido pausada hasta que logres pagar la membresía.',
            'tipoOut'     => 'suspension',
            'etiqueta'    => 'Mensaje de suspensión',
        ];

        if ($mensaje === '') {
            $mensaje = $cfgTipo['defaultMsg'];
        }

        // Garantizar que la columna exista en entornos remotos (ej. TiDB) sin migración manual previa
        if ($cfgTipo['columna'] === 'mensaje_aviso') {
            $stCol = $db->query("SHOW COLUMNS FROM entrenadores_membresias LIKE 'mensaje_aviso'");
            if (!$stCol || !$stCol->fetch()) {
                $db->exec("ALTER TABLE entrenadores_membresias ADD COLUMN mensaje_aviso TEXT NULL AFTER mensaje_bloqueo");
            }
        }

        $col = $cfgTipo['columna'];
        $outPayload = ['mensaje' => $mensaje, 'tipo' => $cfgTipo['tipoOut']];
        if (!$esAviso) {
            $outPayload['mensaje_bloqueo'] = $mensaje;
        }

        if ($alcance === 'todos') {
            // Solo actualizar el ajuste predeterminado global cuando el alcance es "todos"
            $db->prepare("
                INSERT INTO ajustes_globales (clave, valor) VALUES (?, ?)
                ON DUPLICATE KEY UPDATE valor = VALUES(valor)
            ")->execute([$cfgTipo['claveGlobal'], $mensaje]);

            $stIds = $db->query("SELECT id FROM usuarios WHERE rol = 'entrenador' AND estado_cuenta = 'activo' AND activo = 1");
            $activeIds = array_map('intval', $stIds->fetchAll(PDO::FETCH_COLUMN));
            foreach ($activeIds as $eid) {
                obtenerEstadoMembresiaEntrenador($eid);
            }
            if (!empty($activeIds)) {
                $phEnt = implode(',', array_fill(0, count($activeIds), '?'));
                $db->prepare("UPDATE entrenadores_membresias SET `{$col}` = ? WHERE entrenador_id IN ($phEnt)")
                   ->execute(array_merge([$mensaje], $activeIds));
            }
            jsonOut(true, $cfgTipo['etiqueta'] . ' guardado para todos los entrenadores activos.', $outPayload);
        } else {
            $entId = (int)$alcance;
            if ($entId <= 0) {
                jsonOut(false, 'Entrenador inválido.');
            }
            obtenerEstadoMembresiaEntrenador($entId);
            $db->prepare("UPDATE entrenadores_membresias SET `{$col}` = ? WHERE entrenador_id = ?")
               ->execute([$mensaje, $entId]);
            jsonOut(true, $cfgTipo['etiqueta'] . ' guardado para el entrenador seleccionado.', $outPayload);
        }
    } catch (\Throwable $e) {
        error_log('[adminApiGuardarConfiguracion] ' . $e->getMessage());
        jsonOut(false, 'Error interno al guardar la configuración.', [], 500);
    }
}

// ══════════════════════════════════════════════════════════════════
//  ENDPOINTS DE GESTIÓN DE ENTRENADORES Y MEMBRESÍAS
// ══════════════════════════════════════════════════════════════════

function adminApiCrearEntrenador(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());

    $modo      = trim((string)($in['modo'] ?? 'nuevo')); // 'nuevo' | 'promover'
    $codigoRaw = trim((string)($in['codigo_referido'] ?? ''));
    $fechaPago = trim((string)($in['fecha_proximo_pago'] ?? date('Y-m-d', strtotime('+1 month'))));
    $monto     = round(max(0.0, (float)($in['monto_mensualidad'] ?? 25.00)), 2);

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago)) {
        jsonOut(false, 'La fecha de pago debe tener formato YYYY-MM-DD.');
    }

    try {
        $db->beginTransaction();

        if ($modo === 'promover') {
            $targetUid = (int)($in['usuario_id'] ?? 0);
            if ($targetUid <= 0) {
                $db->rollBack();
                jsonOut(false, 'Selecciona un usuario válido para designar como entrenador.');
            }

            $stU = $db->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id = ? LIMIT 1");
            $stU->execute([$targetUid]);
            $uRow = $stU->fetch(PDO::FETCH_ASSOC);
            if (!$uRow) {
                $db->rollBack();
                jsonOut(false, 'El usuario seleccionado no existe.');
            }
            if ($uRow['rol'] === 'admin') {
                $db->rollBack();
                jsonOut(false, 'No puedes modificar el rol del Superadmin.');
            }

            $codigo = adminSanitizarCodigoReferido($codigoRaw, (string)$uRow['nombre'], $targetUid);
            $stDup = $db->prepare("SELECT entrenador_id FROM entrenadores_membresias WHERE codigo_referido = ? AND entrenador_id != ? LIMIT 1");
            $stDup->execute([$codigo, $targetUid]);
            if ($stDup->fetchColumn()) {
                $db->rollBack();
                jsonOut(false, "El código '{$codigo}' ya está asignado a otro entrenador.");
            }

            $db->prepare("UPDATE usuarios SET rol = 'entrenador', estado_cuenta = 'activo', activo = 1 WHERE id = ?")
               ->execute([$targetUid]);

            $db->prepare("
                INSERT INTO entrenadores_membresias (entrenador_id, codigo_referido, monto_mensualidad, moneda, fecha_proximo_pago, dias_gracia, estado_suscripcion, pausado_manualmente)
                VALUES (?, ?, ?, 'USD', ?, 7, 'activa', 0)
                ON DUPLICATE KEY UPDATE
                    codigo_referido = VALUES(codigo_referido),
                    monto_mensualidad = VALUES(monto_mensualidad),
                    fecha_proximo_pago = VALUES(fecha_proximo_pago),
                    estado_suscripcion = 'activa',
                    pausado_manualmente = 0
            ")->execute([$targetUid, $codigo, $monto, $fechaPago]);

            $db->commit();
            jsonOut(true, "Usuario {$uRow['nombre']} designado como entrenador exitosamente.", [
                'entrenador_id'   => $targetUid,
                'codigo_referido' => $codigo,
            ]);
        }

        // Modo 'nuevo': Crear cuenta de entrenador desde cero
        $nombre   = sanitizeInput((string)($in['nombre'] ?? ''), 100);
        $email    = sanitizeEmail((string)($in['email'] ?? ''));
        $password = (string)($in['password'] ?? '');

        if (mb_strlen($nombre) < 2) {
            $db->rollBack();
            jsonOut(false, 'El nombre del entrenador debe tener al menos 2 caracteres.');
        }
        if ($email === '') {
            $db->rollBack();
            jsonOut(false, 'Ingresa un correo electrónico válido.');
        }
        if (strlen($password) < 6) {
            $db->rollBack();
            jsonOut(false, 'La contraseña debe tener al menos 6 caracteres.');
        }

        $um = new UsuarioModel();
        if ($um->emailExists($email)) {
            $db->rollBack();
            jsonOut(false, 'Ese correo electrónico ya está registrado. Usa la opción "Designar usuario existente".');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
        $stIns = $db->prepare("
            INSERT INTO usuarios (nombre, email, password_hash, rol, estado_cuenta, activo, auth_provider)
            VALUES (?, ?, ?, 'entrenador', 'activo', 1, 'local')
        ");
        $stIns->execute([$nombre, $email, $hash]);
        $newEntId = (int)$db->lastInsertId();

        $codigo = adminSanitizarCodigoReferido($codigoRaw, $nombre, $newEntId);
        $stDup = $db->prepare("SELECT entrenador_id FROM entrenadores_membresias WHERE codigo_referido = ? AND entrenador_id != ? LIMIT 1");
        $stDup->execute([$codigo, $newEntId]);
        if ($stDup->fetchColumn()) {
            $codigo .= '-' . $newEntId;
        }

        $db->prepare("
            INSERT INTO entrenadores_membresias (entrenador_id, codigo_referido, monto_mensualidad, moneda, fecha_proximo_pago, dias_gracia, estado_suscripcion, pausado_manualmente)
            VALUES (?, ?, ?, 'USD', ?, 7, 'activa', 0)
        ")->execute([$newEntId, $codigo, $monto, $fechaPago]);

        $db->commit();
        jsonOut(true, "Entrenador {$nombre} creado con el código {$codigo}.", [
            'entrenador_id'   => $newEntId,
            'codigo_referido' => $codigo,
        ]);
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[AdminController][crearEntrenador] ' . $e->getMessage());
        jsonOut(false, 'Error interno al crear o designar el entrenador.', [], 500);
    }
}

function adminApiActualizarMembresia(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());

    $entrenadorId = (int)($in['entrenador_id'] ?? 0);
    if ($entrenadorId <= 0) {
        jsonOut(false, 'ID de entrenador inválido.');
    }

    $stU = $db->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ? AND rol = 'entrenador' LIMIT 1");
    $stU->execute([$entrenadorId]);
    $entRow = $stU->fetch(PDO::FETCH_ASSOC);
    if (!$entRow) {
        jsonOut(false, 'Entrenador no encontrado.');
    }

    $memActual = obtenerEstadoMembresiaEntrenador($entrenadorId);

    $codigoRaw  = trim((string)($in['codigo_referido'] ?? $memActual['codigo_referido']));
    $codigo     = adminSanitizarCodigoReferido($codigoRaw, (string)$entRow['nombre'], $entrenadorId);
    $fechaPago  = trim((string)($in['fecha_proximo_pago'] ?? $memActual['fecha_proximo_pago']));
    $fechaCorte = trim((string)($in['fecha_corte'] ?? ''));
    $monto      = isset($in['monto_mensualidad']) ? round(max(0.0, (float)$in['monto_mensualidad']), 2) : (float)$memActual['monto_mensualidad'];

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaPago)) {
        jsonOut(false, 'Formato de fecha de pago inválido.');
    }

    $diasGracia = (int)($memActual['dias_gracia'] ?? 7);
    if ($fechaCorte !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaCorte)) {
        $pagoTs  = strtotime($fechaPago) ?: time();
        $corteTs = strtotime($fechaCorte) ?: $pagoTs;
        $diasGracia = max(0, min(365, (int)round(($corteTs - $pagoTs) / 86400)));
    }

    $stDup = $db->prepare("SELECT entrenador_id FROM entrenadores_membresias WHERE codigo_referido = ? AND entrenador_id != ? LIMIT 1");
    $stDup->execute([$codigo, $entrenadorId]);
    if ($stDup->fetchColumn()) {
        jsonOut(false, "El código '{$codigo}' ya pertenece a otro entrenador.");
    }

    if (array_key_exists('mensaje_bloqueo', $in)) {
        $mensajeBloqueo = trim(sanitizeInput((string)$in['mensaje_bloqueo'], 255));
        $db->prepare("
            UPDATE entrenadores_membresias
            SET codigo_referido = ?,
                fecha_proximo_pago = ?,
                dias_gracia = ?,
                monto_mensualidad = ?,
                mensaje_bloqueo = ?
            WHERE entrenador_id = ?
        ")->execute([
            $codigo,
            $fechaPago,
            $diasGracia,
            $monto,
            $mensajeBloqueo !== '' ? $mensajeBloqueo : null,
            $entrenadorId,
        ]);
    } else {
        $db->prepare("
            UPDATE entrenadores_membresias
            SET codigo_referido = ?,
                fecha_proximo_pago = ?,
                dias_gracia = ?,
                monto_mensualidad = ?
            WHERE entrenador_id = ?
        ")->execute([
            $codigo,
            $fechaPago,
            $diasGracia,
            $monto,
            $entrenadorId,
        ]);
    }

    $memNueva = obtenerEstadoMembresiaEntrenador($entrenadorId, true);
    jsonOut(true, "Configuración de {$entRow['nombre']} actualizada correctamente.", [
        'membresia' => $memNueva,
    ]);
}

function adminApiGenerarCodigo(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());
    $entrenadorId = (int)($in['entrenador_id'] ?? 0);
    $nombreHint   = trim((string)($in['nombre'] ?? 'COACH'));

    if ($entrenadorId > 0) {
        $stU = $db->prepare("SELECT nombre FROM usuarios WHERE id = ? AND rol = 'entrenador' LIMIT 1");
        $stU->execute([$entrenadorId]);
        $nom = (string)$stU->fetchColumn();
        if (!$nom) {
            jsonOut(false, 'Entrenador no encontrado.');
        }
        $memActual = obtenerEstadoMembresiaEntrenador($entrenadorId);
        $nuevoCodigo = generarCodigoEntrenadorUnico($db, $nom, $entrenadorId, true, (string)($memActual['codigo_referido'] ?? ''));
        $db->prepare("UPDATE entrenadores_membresias SET codigo_referido = ? WHERE entrenador_id = ?")
           ->execute([$nuevoCodigo, $entrenadorId]);
        $memNueva = obtenerEstadoMembresiaEntrenador($entrenadorId, true);
        jsonOut(true, 'Nuevo código generado.', [
            'codigo_referido' => $memNueva['codigo_referido'],
            'link_referido'   => $memNueva['link_referido'],
        ]);
    }

    $nuevoCodigo = generarCodigoEntrenadorUnico($db, $nombreHint, 0, true);
    $baseAppUrl = str_replace(' ', '%20', rtrim((string)APP_URL, '/'));
    jsonOut(true, 'Código disponible generado.', [
        'codigo_referido' => $nuevoCodigo,
        'link_referido'   => $baseAppUrl . '/auth/registro?ref=' . rawurlencode($nuevoCodigo),
    ]);
}

function adminApiTogglePausa(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());

    $target = $in['entrenador_id'] ?? '';
    $pausar = (int)($in['pausar'] ?? 0) === 1 ? 1 : 0;
    $motivo = trim(sanitizeInput((string)($in['mensaje_bloqueo'] ?? ''), 255));

    if ($target === 'todos') {
        $stIds = $db->query("SELECT id FROM usuarios WHERE rol = 'entrenador' AND estado_cuenta = 'activo' AND activo = 1");
        $activeIds = array_map('intval', $stIds->fetchAll(PDO::FETCH_COLUMN));
        foreach ($activeIds as $eid) {
            obtenerEstadoMembresiaEntrenador($eid);
        }

        if (!empty($activeIds)) {
            $phEnt = implode(',', array_fill(0, count($activeIds), '?'));
            if ($motivo !== '') {
                $db->prepare("UPDATE entrenadores_membresias SET pausado_manualmente = ?, estado_suscripcion = ?, mensaje_bloqueo = ? WHERE entrenador_id IN ($phEnt)")
                   ->execute(array_merge([$pausar, $pausar ? 'suspendida' : 'activa', $motivo], $activeIds));
            } else {
                $db->prepare("UPDATE entrenadores_membresias SET pausado_manualmente = ?, estado_suscripcion = ? WHERE entrenador_id IN ($phEnt)")
                   ->execute(array_merge([$pausar, $pausar ? 'suspendida' : 'activa'], $activeIds));
            }
        }

        $msg = $pausar
            ? 'Se ha pausado el acceso web para todos los entrenadores activos.'
            : 'Se ha reanudado el acceso web para todos los entrenadores activos.';
        jsonOut(true, $msg);
    }

    $entrenadorId = (int)$target;
    if ($entrenadorId <= 0) {
        jsonOut(false, 'ID de entrenador inválido.');
    }

    obtenerEstadoMembresiaEntrenador($entrenadorId);

    if ($motivo !== '') {
        $db->prepare("
            UPDATE entrenadores_membresias
            SET pausado_manualmente = ?,
                estado_suscripcion = ?,
                mensaje_bloqueo = ?
            WHERE entrenador_id = ?
        ")->execute([$pausar, $pausar ? 'suspendida' : 'activa', $motivo, $entrenadorId]);
    } else {
        $db->prepare("
            UPDATE entrenadores_membresias
            SET pausado_manualmente = ?,
                estado_suscripcion = ?
            WHERE entrenador_id = ?
        ")->execute([$pausar, $pausar ? 'suspendida' : 'activa', $entrenadorId]);
    }

    $memNueva = obtenerEstadoMembresiaEntrenador($entrenadorId);
    $msg = $pausar
        ? 'El uso de la web para este entrenador ha sido pausado.'
        : 'El uso de la web para este entrenador ha sido reactivado.';

    jsonOut(true, $msg, ['membresia' => $memNueva]);
}

function adminApiReasignarAtleta(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());

    $clienteId    = (int)($in['cliente_id'] ?? 0);
    $entrenadorId = (int)($in['entrenador_id'] ?? 0);

    if ($clienteId <= 0 || $entrenadorId <= 0) {
        jsonOut(false, 'Selecciona un atleta y un entrenador válidos.');
    }

    $stE = $db->prepare("SELECT id, nombre FROM usuarios WHERE id = ? AND rol = 'entrenador' AND estado_cuenta = 'activo' AND activo = 1 LIMIT 1");
    $stE->execute([$entrenadorId]);
    $ent = $stE->fetch(PDO::FETCH_ASSOC);
    if (!$ent) {
        jsonOut(false, 'El entrenador seleccionado no es válido o no está activo.');
    }

    try {
        $db->beginTransaction();
        $db->prepare("UPDATE clientes SET entrenador_id = ? WHERE id = ?")
           ->execute([$entrenadorId, $clienteId]);
        $db->prepare("UPDATE rutinas SET entrenador_id = ? WHERE cliente_id = ?")
           ->execute([$entrenadorId, $clienteId]);
        $db->commit();
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiReasignarAtleta] ' . $e->getMessage());
        jsonOut(false, 'Error interno al reasignar el atleta.', [], 500);
    }

    jsonOut(true, "Atleta asignado al entrenador {$ent['nombre']}.", [
        'entrenador_id'     => $entrenadorId,
        'entrenador_nombre' => $ent['nombre'],
    ]);
}

function adminApiRevocarEntrenador(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());

    $entrenadorId = (int)($in['entrenador_id'] ?? 0);
    $reemplazoId  = (int)($in['reemplazo_id'] ?? 0);

    if ($entrenadorId <= 0) {
        jsonOut(false, 'Entrenador inválido.');
    }

    $stCount = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'entrenador' AND estado_cuenta = 'activo' AND activo = 1");
    if ((int)$stCount->fetchColumn() <= 1) {
        jsonOut(false, 'Debe existir al menos un entrenador activo en el sistema.');
    }

    if ($reemplazoId > 0 && $reemplazoId !== $entrenadorId) {
        $stRep = $db->prepare("SELECT id FROM usuarios WHERE id = ? AND rol = 'entrenador' AND estado_cuenta = 'activo' AND activo = 1 LIMIT 1");
        $stRep->execute([$reemplazoId]);
        if (!$stRep->fetchColumn()) {
            $reemplazoId = 0;
        }
    }
    if ($reemplazoId <= 0 || $reemplazoId === $entrenadorId) {
        $stAlt = $db->prepare("SELECT id FROM usuarios WHERE rol = 'entrenador' AND estado_cuenta = 'activo' AND activo = 1 AND id != ? ORDER BY id ASC LIMIT 1");
        $stAlt->execute([$entrenadorId]);
        $reemplazoId = (int)$stAlt->fetchColumn();
    }
    if ($reemplazoId <= 0) {
        jsonOut(false, 'No hay otro entrenador activo disponible para reasignar los atletas.');
    }

    try {
        $db->beginTransaction();

        $db->prepare("UPDATE clientes SET entrenador_id = ? WHERE entrenador_id = ?")
           ->execute([$reemplazoId, $entrenadorId]);
        $db->prepare("UPDATE rutinas SET entrenador_id = ? WHERE entrenador_id = ?")
           ->execute([$reemplazoId, $entrenadorId]);

        $db->prepare("UPDATE usuarios SET rol = 'cliente' WHERE id = ? AND rol = 'entrenador'")
           ->execute([$entrenadorId]);

        $stChk = $db->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
        $stChk->execute([$entrenadorId]);
        if (!$stChk->fetchColumn()) {
            $db->prepare("INSERT INTO clientes (usuario_id, entrenador_id) VALUES (?, ?)")
               ->execute([$entrenadorId, $reemplazoId]);
        }

        $db->commit();
        jsonOut(true, 'Rol de entrenador revocado y atletas reasignados correctamente.');
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiRevocarEntrenador] ' . $e->getMessage());
        jsonOut(false, 'Error interno al revocar entrenador.', [], 500);
    }
}

function adminApiAprobarEntrenador(): void {
    csrfCheck();
    $db = getDB();
    $in = array_merge($_POST, getJsonPayload());

    $targetUid = (int)($in['usuario_id'] ?? $in['entrenador_id'] ?? 0);
    $accion    = strtolower(trim((string)($in['accion'] ?? '')));

    if ($targetUid <= 0 || !in_array($accion, ['aprobar', 'rechazar'], true)) {
        jsonOut(false, 'Parámetros inválidos para procesar la solicitud.', [], 400);
    }

    try {
        $db->beginTransaction();

        $st = $db->prepare("SELECT id, nombre, email, rol, estado_cuenta FROM usuarios WHERE id = ? AND rol = 'entrenador' LIMIT 1");
        $st->execute([$targetUid]);
        $ent = $st->fetch(PDO::FETCH_ASSOC);

        if (!$ent) {
            $db->rollBack();
            jsonOut(false, 'Solicitud de entrenador no encontrada.', [], 404);
        }

        $nombreEnt = (string)$ent['nombre'];
        $adminUid  = (int)($_SESSION['user_id'] ?? 0);

        if ($accion === 'aprobar') {
            $db->prepare("UPDATE usuarios SET estado_cuenta = 'activo', activo = 1 WHERE id = ? AND rol = 'entrenador'")
               ->execute([$targetUid]);

            // Respetar código si ya existía; si no, generar uno único y habilitar membresía inicial (30 días)
            $stMem = $db->prepare("SELECT codigo_referido FROM entrenadores_membresias WHERE entrenador_id = ? LIMIT 1");
            $stMem->execute([$targetUid]);
            $codigoExist = (string)($stMem->fetchColumn() ?: '');
            $codigo = $codigoExist !== ''
                ? $codigoExist
                : generarCodigoEntrenadorUnico($db, $nombreEnt, $targetUid, false);

            $fechaPago = date('Y-m-d', strtotime('+1 month'));
            $db->prepare("
                INSERT INTO entrenadores_membresias
                    (entrenador_id, codigo_referido, monto_mensualidad, moneda, fecha_proximo_pago, dias_gracia, estado_suscripcion, pausado_manualmente, tienda_activa, tienda_nombre)
                VALUES (?, ?, 25.00, 'USD', ?, 7, 'activa', 0, 0, 'SUPLEMENTOS')
                ON DUPLICATE KEY UPDATE
                    estado_suscripcion = 'activa',
                    pausado_manualmente = 0
            ")->execute([$targetUid, $codigo, $fechaPago]);

            // Marcar como leídas todas las alertas admin asociadas a esta solicitud
            ensureAdminNotifSchema($db);
            $db->prepare("
                UPDATE notificaciones
                SET leido = 1, leida = 1
                WHERE rol_destinatario = 'admin'
                  AND (referencia_id = ? OR entrenador_id = ? OR remitente_id = ?)
            ")->execute([$targetUid, $targetUid, $targetUid]);

            $db->commit();

            // Notificar al nuevo entrenador que su cuenta fue aprobada
            try {
                registrarNotificacionSistema([
                    'remitente_id'     => $adminUid ?: null,
                    'destinatario_id'  => $targetUid,
                    'rol_destinatario' => 'entrenador',
                    'tipo_evento'      => 'activacion_cuenta',
                    'referencia_id'    => $targetUid,
                    'entrenador_id'    => $targetUid,
                    'mensaje'          => '¡Tu solicitud de entrenador fue aprobada! Tu código de invitación es ' . $codigo . '.',
                    'enlace'           => APP_URL . '/entrenador/dashboard',
                    'metadatos'        => ['codigo_referido' => $codigo],
                ]);
            } catch (\Throwable $eNotif) {
                error_log('[adminApiAprobarEntrenador] Notif bienvenida: ' . $eNotif->getMessage());
            }

            jsonOut(true, "Entrenador {$nombreEnt} aprobado y activado. Código asignado: {$codigo}", [
                'entrenador_id'   => $targetUid,
                'codigo_referido' => $codigo,
                'estado_cuenta'   => 'activo',
            ]);
        }

        // Rechazar solicitud
        $db->prepare("UPDATE usuarios SET estado_cuenta = 'rechazado', activo = 0 WHERE id = ? AND rol = 'entrenador'")
           ->execute([$targetUid]);

        ensureAdminNotifSchema($db);
        $db->prepare("
            UPDATE notificaciones
            SET leido = 1, leida = 1
            WHERE rol_destinatario = 'admin'
              AND (referencia_id = ? OR entrenador_id = ? OR remitente_id = ?)
        ")->execute([$targetUid, $targetUid, $targetUid]);

        $db->commit();
        jsonOut(true, "Solicitud de entrenador de {$nombreEnt} rechazada.", [
            'entrenador_id' => $targetUid,
            'estado_cuenta' => 'rechazado',
        ]);
    } catch (\Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log('[adminApiAprobarEntrenador] ' . $e->getMessage());
        jsonOut(false, 'No se pudo procesar la solicitud de entrenador.', [], 500);
    }
}

function adminApiNotificaciones(): void {
    $adminUid = (int)($_SESSION['user_id'] ?? 0);
    $notifs = obtenerNotificacionesUsuario($adminUid, 'admin', 20, true);
    $unread = contarNotificacionesNoLeidas($adminUid, 'admin');
    jsonOut(true, 'OK', ['notificaciones' => $notifs, 'unread_count' => $unread]);
}

function adminApiMarcarNotificacion(): void {
    csrfCheck();
    try {
        $in = array_merge($_POST, getJsonPayload());
        $id = (int)($in['id'] ?? 0);
        $adminUid = (int)($_SESSION['user_id'] ?? 0);
        if ($id > 0 && $adminUid > 0) {
            marcarNotificacionLeida($id, $adminUid);
        }
        jsonOut(true, 'OK');
    } catch (\Throwable $e) {
        error_log('[adminApiMarcarNotificacion] ' . $e->getMessage());
        jsonOut(false, 'No se pudo actualizar la notificación.', [], 500);
    }
}

function adminApiLeerNotificaciones(): void {
    csrfCheck();
    try {
        marcarTodasNotificacionesLeidas((int)($_SESSION['user_id'] ?? 0), 'admin');
        jsonOut(true, 'OK');
    } catch (\Throwable $e) {
        error_log('[adminApiLeerNotificaciones] ' . $e->getMessage());
        jsonOut(false, 'No se pudieron marcar las notificaciones.', [], 500);
    }
}

// ══════════════════════════════════════════════════════════════════
//  CATÁLOGO GLOBAL DE EJERCICIOS, IMPORTADOR IA Y RESPALDO (SUPER ADMIN)
// ══════════════════════════════════════════════════════════════════

function adminApiCatalogoList(): void {
    CatalogoService::apiList(getDB(), null);
}

function adminApiCatalogoAgregar(): void {
    csrfCheck();
    CatalogoService::apiAgregar(getDB(), null);
}

function adminApiCatalogoEditar(): void {
    csrfCheck();
    CatalogoService::apiEditar(getDB(), null);
}

function adminApiCatalogoBorrar(): void {
    csrfCheck();
    CatalogoService::apiBorrar(getDB(), null);
}

function adminApiImportarHistorialAnalizar(): void {
    ImportadorService::analizar(getDB(), null);
}

function adminApiImportarHistorialGuardar(): void {
    ImportadorService::guardar(getDB(), null);
}

function adminDescargarRespaldoDb(): void {
    csrfCheck();
    try {
        $db = getDB();
        $alcance = strtolower(trim((string)($_POST['alcance'] ?? 'global')));
        $targetEntId = (int)($_POST['entrenador_id'] ?? 0);

        if ($alcance === 'entrenador' && $targetEntId > 0) {
            $stChk = $db->prepare("SELECT nombre FROM usuarios WHERE id = ? AND rol = 'entrenador' LIMIT 1");
            $stChk->execute([$targetEntId]);
            $entNom = $stChk->fetchColumn();
            if (!$entNom) {
                jsonOut(false, 'Entrenador seleccionado no válido.', [], 404);
            }
            $slug = preg_replace('/[^a-z0-9]+/i', '_', strtolower(trim((string)$entNom)));
            $slug = trim((string)$slug, '_') ?: ('ent_' . $targetEntId);
            $filename = 'atlas_fit_entrenador_' . $slug . '_' . date('Y-m-d_His') . '.sql.gz';
            BackupService::descargarRespaldoEntrenador($db, $targetEntId, $filename);
            return;
        }

        $filename = 'atlas_fit_global_' . date('Y-m-d_His') . '.sql.gz';
        BackupService::descargarRespaldo($db, $filename);
    } catch (\Throwable $e) {
        error_log('[adminDescargarRespaldoDb] ' . $e->getMessage());
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok'  => false,
            'msg' => 'Error interno al generar la copia de seguridad.'
        ]);
        exit;
    }
}
