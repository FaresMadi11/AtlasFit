<?php
// core/helpers.php

/** Escapa output HTML de forma segura */
function e(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Elimina tildes/acentos de una cadena UTF-8 */
function sinAcentos(string $s): string {
    $from = ['Á','É','Í','Ó','Ú','á','é','í','ó','ú','Ñ','ñ','Ü','ü'];
    $to   = ['A','E','I','O','U','a','e','i','o','u','N','n','U','u'];
    return str_replace($from, $to, $s);
}

/** Obtiene y decodifica de forma segura el payload JSON enviado en la petición */
function getJsonPayload(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return [];
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

/** Respuesta JSON estándar y termina (compatible con ok/msg y success/message) */
function jsonOut(bool $ok, string $msg, array $extra = [], int $status = 200): never {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(array_merge([
        'ok'      => $ok,
        'success' => $ok,
        'msg'     => $msg,
        'message' => $msg
    ], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

/** Respuesta JSON directa con payload arbitrario */
function jsonResponse(array $data, int $status = 200): never {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (!headers_sent()) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Formatear fecha en español ('Lun 12 Oct' o 'Lun 12 Oct 2026') */
if (!function_exists('fechaES')) {
    function fechaES(string $fecha, bool $conAno = false): string {
        if (!$fecha || $fecha === '0000-00-00') return '';
        $meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $dias  = ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'];
        $ts    = strtotime($fecha);
        if (!$ts) return $fecha;
        $res   = $dias[(int)date('w', $ts)] . ' ' . date('j', $ts) . ' ' . $meses[(int)date('n', $ts)];
        return $conAno ? ($res . ' ' . date('Y', $ts)) : $res;
    }
}


/** Genera una URL absoluta limpia hacia una ruta interna */
function url(string $path = ''): string {
    $cleanPath = ltrim($path, '/');
    return $cleanPath !== '' ? (APP_URL . '/' . $cleanPath) : APP_URL;
}

/** Genera una URL absoluta limpia hacia un recurso/asset estático */
function asset(string $path = ''): string {
    return url($path);
}

/**
 * Devuelve la marca de tiempo de modificación de un archivo local (SSOT de cache-busting).
 */
function xpAssetVer(string $relPath): string {
    static $cache = [];
    $clean = ltrim(str_replace('\\', '/', $relPath), '/');
    if (isset($cache[$clean])) {
        return $cache[$clean];
    }
    $full = (defined('BASE_DIR') ? BASE_DIR : dirname(__DIR__)) . '/' . $clean;
    $mt = @filemtime($full);
    return $cache[$clean] = ($mt !== false ? (string)$mt : '1');
}

/**
 * Calcula un hash determinista corto de la build actual (CSS, JS, iconos, manifest, SW y layout).
 * Cambia automáticamente en cada despliegue sin editar versiones manualmente.
 */
function xpAppBuildVersion(): string {
    static $ver = null;
    if ($ver !== null) {
        return $ver;
    }
    $files = [
        'sw.js',
        'manifest.webmanifest',
        'assets/css/style.css',
        'assets/css/custom-select.css',
        'assets/css/custom-date.css',
        'assets/js/app.js',
        'assets/js/pwa-sync.js',
        'assets/js/custom-select.js',
        'assets/js/custom-date.js',
        'assets/vendor/tabler-icons/tabler-icons.min.css',
        'assets/vendor/tabler-icons/fonts/tabler-icons.woff2',
        'assets/vendor/sweetalert2/sweetalert2.all.min.js',
        'assets/vendor/animejs/anime.min.js',
        'assets/img/icon-192.png',
        'assets/img/icon-512.png',
        'assets/img/icon-maskable-512.png',
        'assets/img/apple-touch-icon.png',
        'assets/img/favicon-32.png',
        'assets/img/favicon.svg',
        'favicon.ico',
        'views/layout/header.php',
        'views/auth/login.php',
    ];
    $parts = [];
    foreach ($files as $f) {
        $parts[] = $f . ':' . xpAssetVer($f);
    }
    return $ver = substr(md5(implode('|', $parts)), 0, 10);
}

/**
 * Nombre único de caché para Service Worker y pwa-sync.js alineado a la build actual.
 */
function xpAppCacheName(): string {
    return 'atlas-fit-' . xpAppBuildVersion();
}

/** Redirige y termina */
function redirect(string $path): never {
    header('Location: ' . url($path));
    exit;
}

/** Verifica que el usuario esté logueado y activo; si no, redirige al login */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        redirect('auth/login');
    }
    // Verificación de estado de cuenta para atletas y entrenadores (cacheada 60s en sesión)
    if (($_SESSION['rol'] ?? '') !== 'admin') {
        $now = time();
        $lastCheck = (int)($_SESSION['_acc_ok_ts'] ?? 0);
        if (($now - $lastCheck) > 60) {
            try {
                $db = getDB();
                $stmt = $db->prepare("SELECT estado_cuenta, activo FROM usuarios WHERE id = ? LIMIT 1");
                $stmt->execute([(int)$_SESSION['user_id']]);
                $accRow = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!$accRow || ($accRow['estado_cuenta'] ?? '') !== 'activo' || empty($accRow['activo'])) {
                    $rolPrev = (string)($_SESSION['rol'] ?? 'cliente');
                    $_SESSION = [];
                    if (ini_get('session.use_cookies')) {
                        $params = session_get_cookie_params();
                        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
                    }
                    session_destroy();
                    redirect('auth/login?standby=1' . ($rolPrev === 'entrenador' ? '&rol=entrenador' : ''));
                }
                $_SESSION['_acc_ok_ts'] = $now;
            } catch (\Throwable $e) {
                // Failsafe DB
            }
        }
    }
}

/**
 * Libera el bloqueo de archivo de sesión PHP en peticiones GET de solo lectura.
 * Permite que cambios rápidos de módulo y peticiones AJAX en el teléfono se ejecuten en paralelo sin hacer cola.
 */
function releaseSessionLockIfReadOnly(): void {
    if (
        session_status() === PHP_SESSION_ACTIVE &&
        ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' &&
        empty($_SESSION['flash'])
    ) {
        session_write_close();
    }
}

/**
 * Obtiene y calcula en tiempo real el estado de la membresía mensual de un entrenador.
 * Reglas de negocio:
 *  - Recordatorio: desde 7 días antes de fecha_proximo_pago hasta el día de pago.
 *  - Período de gracia: desde 1 hasta 7 días después de fecha_proximo_pago.
 *  - Suspensión: a partir del día 8 después de fecha_proximo_pago, o si pausado_manualmente = 1.
 */
function ensureStoreAndMembershipSchema(PDO $db): void {
    static $ensured = false;
    if ($ensured) return;
    $ensured = true;

    try {
        $colsMem = [];
        $stMem = $db->query("SHOW COLUMNS FROM entrenadores_membresias");
        if ($stMem) {
            foreach ($stMem->fetchAll(PDO::FETCH_ASSOC) as $c) {
                $colsMem[$c['Field']] = true;
            }
        }
        if (!isset($colsMem['mensaje_aviso'])) {
            $db->exec("ALTER TABLE entrenadores_membresias ADD COLUMN mensaje_aviso VARCHAR(255) NULL DEFAULT NULL");
        }
        $addedTiendaActiva = false;
        if (!isset($colsMem['tienda_activa'])) {
            $db->exec("ALTER TABLE entrenadores_membresias ADD COLUMN tienda_activa TINYINT(1) NOT NULL DEFAULT 0");
            $addedTiendaActiva = true;
        }
        if (!isset($colsMem['tienda_nombre'])) {
            $db->exec("ALTER TABLE entrenadores_membresias ADD COLUMN tienda_nombre VARCHAR(80) NOT NULL DEFAULT 'SUPLEMENTOS'");
        }
        if (!isset($colsMem['whatsapp_numero'])) {
            $db->exec("ALTER TABLE entrenadores_membresias ADD COLUMN whatsapp_numero VARCHAR(30) NULL DEFAULT NULL");
            // Migrar el WhatsApp legado al entrenador principal (#1) normalizado sin '+'
            try {
                $legacyNum = (string)($db->query("SELECT valor FROM ajustes_globales WHERE clave = 'whatsapp_numero' LIMIT 1")->fetchColumn() ?: '');
                $normNum = normalizarTelefonoWhatsApp($legacyNum);
                if ($normNum !== '') {
                    $db->prepare("UPDATE entrenadores_membresias SET whatsapp_numero = ? WHERE entrenador_id = 1 AND (whatsapp_numero IS NULL OR whatsapp_numero = '')")
                       ->execute([$normNum]);
                }
            } catch (\Throwable $e) {}
        }
        if (!isset($colsMem['whatsapp_mensaje'])) {
            $db->exec("ALTER TABLE entrenadores_membresias ADD COLUMN whatsapp_mensaje VARCHAR(255) NULL DEFAULT NULL");
            try {
                $legacyMsg = trim((string)($db->query("SELECT valor FROM ajustes_globales WHERE clave = 'whatsapp_mensaje' LIMIT 1")->fetchColumn() ?: ''));
                if ($legacyMsg !== '') {
                    $db->prepare("UPDATE entrenadores_membresias SET whatsapp_mensaje = ? WHERE entrenador_id = 1 AND (whatsapp_mensaje IS NULL OR whatsapp_mensaje = '')")
                       ->execute([$legacyMsg]);
                }
            } catch (\Throwable $e) {}
        }
        if ($addedTiendaActiva) {
            $db->exec("UPDATE entrenadores_membresias SET tienda_activa = 0");
        }

        foreach (['tienda_secciones', 'tienda_productos'] as $tbl) {
            $stT = $db->query("SHOW COLUMNS FROM {$tbl} LIKE 'entrenador_id'");
            if ($stT && !$stT->fetch()) {
                $db->exec("ALTER TABLE {$tbl} ADD COLUMN entrenador_id INT UNSIGNED NULL DEFAULT NULL AFTER id");
                $db->exec("UPDATE {$tbl} SET entrenador_id = 1 WHERE entrenador_id IS NULL");
            }
        }
    } catch (\Throwable $e) {
        error_log('[ensureStoreAndMembershipSchema] ' . $e->getMessage());
    }
}

function normalizarTelefonoWhatsApp(?string $num): string {
    $digits = preg_replace('/\D+/', '', trim((string)$num)) ?? '';
    if ($digits === '') return '';
    if (str_starts_with($digits, '00')) {
        $digits = substr($digits, 2);
    }
    if (preg_match('/^0(4\d{9})$/', $digits, $m)) {
        $digits = '58' . $m[1];
    }
    return $digits;
}

function construirUrlWhatsAppProducto(?string $numeroRaw, ?string $template, string $productoNombre, int $productoId): ?string {
    $digits = normalizarTelefonoWhatsApp($numeroRaw);
    if (strlen($digits) < 7) {
        return null;
    }
    $tpl = trim((string)$template);
    if ($tpl === '') {
        $tpl = 'Hola, me interesa adquirir: {producto}';
    }
    $baseMsg = str_contains($tpl, '{producto}')
        ? str_replace('{producto}', $productoNombre, $tpl)
        : ($tpl . ' ' . $productoNombre);
    $publicUrl = url('public/producto?id=' . $productoId);
    $fullMsg   = trim($baseMsg) . "\n\n" . $publicUrl;
    return 'https://wa.me/' . $digits . '?text=' . rawurlencode($fullMsg);
}

function generarCodigoEntrenadorUnico(PDO $db, string $nombre, int $excludeEntId = 0, bool $aleatorio = false, string $evitarCodigo = ''): string {
    $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', sinAcentos($nombre)) ?? '');
    $prefix = substr($clean !== '' ? $clean : 'COACH', 0, 6);
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $evitarUpper = strtoupper(trim($evitarCodigo));

    $stCheck = $db->prepare("SELECT 1 FROM entrenadores_membresias WHERE UPPER(codigo_referido) = ? AND entrenador_id != ? LIMIT 1");
    if (!$aleatorio) {
        $candidato = $prefix . '-FIT';
        $stCheck->execute([$candidato, $excludeEntId]);
        if ($candidato !== $evitarUpper && !$stCheck->fetchColumn()) {
            return $candidato;
        }
    }

    for ($i = 0; $i < 25; $i++) {
        $suf = '';
        for ($j = 0; $j < 4; $j++) {
            $suf .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }
        $candidato = $prefix . '-' . $suf;
        if ($evitarUpper !== '' && $candidato === $evitarUpper) {
            continue;
        }
        $stCheck->execute([$candidato, $excludeEntId]);
        if (!$stCheck->fetchColumn()) {
            return $candidato;
        }
    }
    return $prefix . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
}

function obtenerEstadoMembresiaEntrenador(int $entrenadorId, bool $forceRefresh = false): array {
    static $cache = [];
    if (!$forceRefresh && isset($cache[$entrenadorId])) {
        return $cache[$entrenadorId];
    }

    $default = [
        'entrenador_id'        => $entrenadorId,
        'codigo_referido'      => 'COACH-' . $entrenadorId,
        'link_referido'        => url('auth/registro?ref=COACH-' . $entrenadorId),
        'monto_mensualidad'    => 25.00,
        'moneda'               => 'USD',
        'fecha_proximo_pago'   => date('Y-m-d', strtotime('+30 days')),
        'fecha_limite_gracia'  => date('Y-m-d', strtotime('+37 days')),
        'dias_gracia'          => 7,
        'dias_para_pago'       => 30,
        'dias_gracia_restantes'=> 7,
        'pausado_manualmente'  => 0,
        'estado_calculado'     => 'activa',
        'bloqueado'            => false,
        'mostrar_banner'       => false,
        'tipo_banner'          => null,
        'titulo_alerta'        => null,
        'mensaje_alerta'       => null,
        'tienda_activa'        => 0,
        'tienda_nombre'        => 'SUPLEMENTOS',
        'whatsapp_numero'      => '',
        'whatsapp_mensaje'     => 'Hola, me interesa adquirir: {producto}',
    ];

    if ($entrenadorId <= 0) {
        return $default;
    }

    try {
        $db = getDB();
        $st = $db->prepare("SELECT * FROM entrenadores_membresias WHERE entrenador_id = ? LIMIT 1");
        $st->execute([$entrenadorId]);
        $row = $st->fetch(PDO::FETCH_ASSOC);

        if (!$row || !array_key_exists('tienda_activa', $row)) {
            ensureStoreAndMembershipSchema($db);
            $st->execute([$entrenadorId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
        }

        if (!$row) {
            $stU = $db->prepare("SELECT nombre FROM usuarios WHERE id = ? LIMIT 1");
            $stU->execute([$entrenadorId]);
            $nom = (string)($stU->fetchColumn() ?: 'COACH');
            $code = generarCodigoEntrenadorUnico($db, $nom, $entrenadorId, false);

            $fechaInit = date('Y-m-d', strtotime('+30 days'));
            $db->prepare("
                INSERT IGNORE INTO entrenadores_membresias 
                (entrenador_id, codigo_referido, monto_mensualidad, moneda, fecha_proximo_pago, dias_gracia, estado_suscripcion, pausado_manualmente, tienda_activa, tienda_nombre)
                VALUES (?, ?, 25.00, 'USD', ?, 7, 'activa', 0, 0, 'SUPLEMENTOS')
            ")->execute([$entrenadorId, $code, $fechaInit]);

            $st->execute([$entrenadorId]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
        }

        if (!$row) {
            return $default;
        }

        $codigo      = (string)$row['codigo_referido'];
        $fechaPago   = (string)$row['fecha_proximo_pago'];
        $diasGracia  = isset($row['dias_gracia']) ? max(0, (int)$row['dias_gracia']) : 7;
        $pausadoMan  = (int)($row['pausado_manualmente'] ?? 0);
        $msgCustom   = trim((string)($row['mensaje_bloqueo'] ?? ''));
        if ($msgCustom === '') {
            try {
                $stGlobalMsg = $db->query("SELECT valor FROM ajustes_globales WHERE clave = 'mensaje_pausa_entrenadores' LIMIT 1");
                $valGlobalMsg = $stGlobalMsg ? trim((string)$stGlobalMsg->fetchColumn()) : '';
                if ($valGlobalMsg !== '') {
                    $msgCustom = $valGlobalMsg;
                }
            } catch (\Throwable $e) {}
        }

        $hoyTs       = strtotime(date('Y-m-d'));
        $pagoTs      = strtotime($fechaPago) ?: $hoyTs;
        $avisoTs     = strtotime('-7 days', $pagoTs);
        $fechaAviso  = date('Y-m-d', $avisoTs);
        $limiteTs    = strtotime("+{$diasGracia} days", $pagoTs);
        $fechaLimite = date('Y-m-d', $limiteTs);

        $diasParaPago = (int)round(($pagoTs - $hoyTs) / 86400);
        $diasGraciaRestantes = (int)round(($limiteTs - $hoyTs) / 86400);

        $estadoCalculado = 'activa';
        $bloqueado       = false;
        $mostrarBanner   = false;
        $tipoBanner      = null;
        $tituloAlerta    = null;
        $mensajeAlerta   = null;

        $fechaAvisoFmt  = date('d/m/Y', $avisoTs);
        $fechaPagoFmt   = date('d/m/Y', $pagoTs);
        $fechaLimiteFmt = date('d/m/Y', $limiteTs);

        if ($pausadoMan === 1) {
            $estadoCalculado = 'pausada_manual';
            $bloqueado       = true;
            $tituloAlerta    = 'Web en Pausa';
            $mensajeAlerta   = $msgCustom !== ''
                ? $msgCustom
                : 'El uso de la web ha sido pausado hasta que logres completar el pago de tu membresía mensual.';
        } elseif ($diasParaPago < -$diasGracia) {
            // Pasó más de 1 semana después de la fecha de pago -> Suspensión automática
            $estadoCalculado = 'suspendida';
            $bloqueado       = true;
            $tituloAlerta    = 'Membresía Vencida — Web en Suspensión';
            $mensajeAlerta   = $msgCustom !== ''
                ? $msgCustom
                : "El uso de la web ha sido pausado temporalmente porque el plazo de pago venció el {$fechaLimiteFmt}. Se reactivará en cuanto completes el pago de tu membresía.";
        } elseif ($diasParaPago < 0) {
            // Dentro de la semana de gracia post-vencimiento (días -1 a -7)
            $diasVencidos    = abs($diasParaPago);
            $estadoCalculado = 'en_gracia';
            $bloqueado       = false;
            $mostrarBanner   = true;
            $tipoBanner      = 'gracia';
            $tituloAlerta    = 'Período de Gracia de Membresía';
            if ($diasGraciaRestantes <= 0) {
                $mensajeAlerta = "Tu fecha de pago fue el {$fechaPagoFmt}. Hoy es tu último día de gracia antes de que la web se ponga en suspensión.";
            } else {
                $dTxt = $diasGraciaRestantes === 1 ? '1 día' : "{$diasGraciaRestantes} días";
                $mensajeAlerta = "Tu membresía venció el {$fechaPagoFmt}. Tienes {$dTxt} de prórroga (hasta el {$fechaLimiteFmt}) para realizar el pago antes de que la web se pause.";
            }
        } elseif ($diasParaPago <= 7) {
            // Semana previa a la fecha de pago (días 0 a 7 antes)
            $estadoCalculado = 'recordatorio';
            $bloqueado       = false;
            $mostrarBanner   = true;
            $tipoBanner      = 'recordatorio';
            $tituloAlerta    = 'Recordatorio de Membresía';
            $msgAvisoCustom  = trim((string)($row['mensaje_aviso'] ?? ''));
            if ($msgAvisoCustom === '') {
                try {
                    $stGlobalAv = $db->query("SELECT valor FROM ajustes_globales WHERE clave = 'mensaje_aviso_entrenadores' LIMIT 1");
                    $valGlobalAv = $stGlobalAv ? trim((string)$stGlobalAv->fetchColumn()) : '';
                    if ($valGlobalAv !== '') {
                        $msgAvisoCustom = $valGlobalAv;
                    }
                } catch (\Throwable $e) {}
            }
            if ($msgAvisoCustom !== '') {
                $mensajeAlerta = $msgAvisoCustom;
            } elseif ($diasParaPago === 0) {
                $mensajeAlerta = "Hoy ({$fechaPagoFmt}) es tu fecha de pago de membresía mensual. Recuerda que cuentas con 7 días de chance hasta el {$fechaLimiteFmt}.";
            } else {
                $dTxt = $diasParaPago === 1 ? 'mañana' : "en {$diasParaPago} días";
                $mensajeAlerta = "Recordatorio: Tu fecha de pago de la membresía mensual es {$dTxt} ({$fechaPagoFmt}).";
            }
        }

        // Sincronizar columna estado_suscripcion en BD si cambió
        $dbEstado = match ($estadoCalculado) {
            'pausada_manual' => 'pausada_manual',
            'suspendida'     => 'suspendida',
            'en_gracia'      => 'en_gracia',
            default          => 'activa',
        };
        if (($row['estado_suscripcion'] ?? '') !== $dbEstado) {
            $db->prepare("UPDATE entrenadores_membresias SET estado_suscripcion = ? WHERE id = ?")
               ->execute([$dbEstado, (int)$row['id']]);
        }

        $baseAppUrl = str_replace(' ', '%20', rtrim((string)APP_URL, '/'));
        $res = [
            'id'                    => (int)$row['id'],
            'entrenador_id'         => $entrenadorId,
            'codigo_referido'       => $codigo,
            'link_referido'         => $baseAppUrl . '/auth/registro?ref=' . rawurlencode($codigo),
            'monto_mensualidad'     => (float)($row['monto_mensualidad'] ?? 25.00),
            'moneda'                => (string)($row['moneda'] ?? 'USD'),
            'fecha_aviso'           => $fechaAviso,
            'fecha_aviso_fmt'       => $fechaAvisoFmt,
            'fecha_proximo_pago'    => $fechaPago,
            'fecha_proximo_pago_fmt'=> $fechaPagoFmt,
            'fecha_limite_gracia'   => $fechaLimite,
            'fecha_limite_fmt'      => $fechaLimiteFmt,
            'dias_gracia'           => $diasGracia,
            'dias_para_pago'        => $diasParaPago,
            'dias_gracia_restantes' => max(0, $diasGraciaRestantes),
            'pausado_manualmente'   => $pausadoMan,
            'estado_calculado'      => $estadoCalculado,
            'bloqueado'             => $bloqueado,
            'mostrar_banner'        => $mostrarBanner,
            'tipo_banner'           => $tipoBanner,
            'titulo_alerta'         => $tituloAlerta,
            'mensaje_alerta'        => $mensajeAlerta,
            'mensaje_bloqueo'       => trim((string)($row['mensaje_bloqueo'] ?? '')),
            'mensaje_aviso'         => trim((string)($row['mensaje_aviso'] ?? '')),
            'notas_admin'           => $row['notas_admin'] ?? null,
            'tienda_activa'         => (int)($row['tienda_activa'] ?? 0),
            'tienda_nombre'         => ($tn = trim((string)($row['tienda_nombre'] ?? ''))) !== '' ? $tn : 'SUPLEMENTOS',
            'whatsapp_numero'       => normalizarTelefonoWhatsApp($row['whatsapp_numero'] ?? ''),
            'whatsapp_mensaje'      => ($wm = trim((string)($row['whatsapp_mensaje'] ?? ''))) !== '' ? $wm : 'Hola, me interesa adquirir: {producto}',
        ];
        $cache[$entrenadorId] = $res;
        return $res;
    } catch (\Throwable $e) {
        error_log('[obtenerEstadoMembresiaEntrenador] ' . $e->getMessage());
        return $default;
    }
}

/**
 * Busca un entrenador activo a partir de su código de referido / link.
 */
function resolverEntrenadorPorCodigo(?string $codigo): ?array {
    $clean = strtoupper(trim((string)$codigo));
    if ($clean === '') return null;

    try {
        $db = getDB();
        $st = $db->prepare("
            SELECT em.entrenador_id, em.codigo_referido, u.nombre AS entrenador_nombre, u.avatar_url
            FROM entrenadores_membresias em
            JOIN usuarios u ON u.id = em.entrenador_id
            WHERE UPPER(TRIM(em.codigo_referido)) = ?
              AND u.rol = 'entrenador'
              AND u.activo = 1
              AND u.estado_cuenta = 'activo'
            LIMIT 1
        ");
        $st->execute([$clean]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    } catch (\Throwable $e) {
        return null;
    }
}

/** Verifica rol, si no tiene acceso redirige. Si es entrenador, valida que su membresía no esté suspendida. */
function requireRole(string $rol): void {
    requireLogin();
    $currentRol = $_SESSION['rol'] ?? '';
    if ($currentRol !== $rol) {
        if ($currentRol === 'admin') {
            redirect('admin/dashboard');
        } elseif ($currentRol === 'entrenador') {
            redirect('entrenador/dashboard');
        } elseif ($currentRol === 'cliente') {
            redirect('cliente/dashboard');
        }
        redirect('auth/login');
    }

    if ($rol === 'entrenador') {
        $entId = (int)($_SESSION['user_id'] ?? 0);
        $membresia = obtenerEstadoMembresiaEntrenador($entId);
        if (!empty($membresia['bloqueado'])) {
            $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                   || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
                   || (!empty($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'))
                   || str_starts_with((string)($GLOBALS['action'] ?? ''), 'api-');

            if ($isAjax) {
                jsonOut(false, $membresia['mensaje_alerta'] ?? 'La web ha sido pausada hasta completar el pago de la membresía.', [
                    'suspended' => true,
                    'estado'    => $membresia['estado_calculado']
                ], 403);
            }

            $pageTitle = 'Membresía en Pausa';
            require BASE_DIR . '/views/entrenador/membresia_suspendida.php';
            exit;
        }
    }
}

/** Flash message helper */
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/** Envía un correo vía API HTTPS transaccional (puerto 443, inmune a bloqueos SMTP en cloud). */
function _sendEmailViaHttpsApi(string $to, string $subject, string $body, string $fromEmail, string $fromName): ?bool {
    if (!function_exists('curl_init')) {
        return null;
    }

    $brevoKey  = trim((string)env('BREVO_API_KEY', ''));
    $resendKey = trim((string)env('RESEND_API_KEY', ''));

    if ($brevoKey === '' && $resendKey === '') {
        return null;
    }

    if ($brevoKey !== '') {
        $endpoint = 'https://api.brevo.com/v3/smtp/email';
        $headers  = [
            'api-key: ' . $brevoKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        $payload  = json_encode([
            'sender'      => ['name' => $fromName, 'email' => $fromEmail],
            'to'          => [['email' => $to]],
            'subject'     => $subject,
            'htmlContent' => $body,
        ], JSON_UNESCAPED_UNICODE);
    } else {
        $endpoint = 'https://api.resend.com/emails';
        $headers  = [
            'Authorization: Bearer ' . $resendKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        $payload  = json_encode([
            'from'    => $fromName . ' <' . $fromEmail . '>',
            'to'      => [$to],
            'subject' => $subject,
            'html'    => $body,
        ], JSON_UNESCAPED_UNICODE);
    }

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 4,
        CURLOPT_TIMEOUT        => 6,
    ]);
    $resp     = curl_exec($ch);
    $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr === '' && $httpCode >= 200 && $httpCode < 300) {
        return true;
    }

    error_log('[Xprod Fit] Mail API Error (' . $httpCode . '): ' . ($curlErr ?: (string)$resp));
    return false;
}

/** Envía un correo priorizando API HTTPS (Brevo/Resend) y con respaldo PHPMailer SMTP */
function sendEmail(string $to, string $subject, string $body): bool {
    $fromName  = trim((string)env('MAIL_FROM_NAME', 'Atlas Fit')) ?: 'Atlas Fit';
    $mailHost  = trim((string)env('MAIL_HOST', 'smtp.gmail.com'));
    $mailUser  = trim((string)env('MAIL_USER', ''));
    $fromEmail = trim((string)env('MAIL_FROM', '')) ?: ($mailUser ?: 'noreply@atlasfit.com');

    // Prioridad 1: API HTTPS transaccional (no bloqueada en Render/cloud PaaS)
    $apiResult = _sendEmailViaHttpsApi($to, $subject, $body, $fromEmail, $fromName);
    if ($apiResult === true) {
        return true;
    }

    // Prioridad 2: Fallback PHPMailer SMTP con timeout corto
    if ($mailUser === '' || trim((string)env('MAIL_PASS', '')) === '') {
        return false;
    }

    require_once BASE_DIR . '/libs/PHPMailer/Exception.php';
    require_once BASE_DIR . '/libs/PHPMailer/PHPMailer.php';
    require_once BASE_DIR . '/libs/PHPMailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = $mailHost;
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailUser;
        $mail->Password   = env('MAIL_PASS', '');
        $mailPort         = (int)env('MAIL_PORT', '587');
        $mail->SMTPSecure = ($mailPort === 465)
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $mailPort;
        $mail->Timeout    = 8;

        // Alinear From con MAIL_USER en Gmail para evitar rechazos DMARC/SPF
        $smtpFrom = $fromEmail;
        if (str_contains(strtolower($mailHost), 'gmail.com') && filter_var($mailUser, FILTER_VALIDATE_EMAIL)) {
            $smtpFrom = $mailUser;
            if (strcasecmp($fromEmail, $mailUser) !== 0 && filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($fromEmail, $fromName);
            }
        }

        $mail->setFrom($smtpFrom, $fromName);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->CharSet = 'UTF-8';

        return $mail->send();
    } catch (Exception $e) {
        error_log('[Xprod Fit] Mail SMTP Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Obtiene la tasa oficial del BCV con caché local de alta resiliencia.
 * Evita bloqueos en redes móviles y fallos en navegadores de teléfonos.
 */
function obtenerTasaBCV(): float {
    $cacheDir = BASE_DIR . '/storage';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0777, true);
    }
    $cacheFile = $cacheDir . '/tasa_bcv.json';

    // 1. Si existe caché reciente (menos de 2 horas), usarlo de inmediato
    if (file_exists($cacheFile)) {
        $cached = @json_decode((string)file_get_contents($cacheFile), true);
        if (!empty($cached['tasa']) && is_numeric($cached['tasa']) && (float)$cached['tasa'] > 0) {
            $tiempo = $cached['actualizado_en'] ?? 0;
            if ((time() - $tiempo) < 7200) {
                return (float)$cached['tasa'];
            }
        }
    }

    $tasa = 0.0;

    // 2. Intentar consultar DolarAPI Oficial con timeout corto
    try {
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 3,
                'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        $raw = @file_get_contents('https://ve.dolarapi.com/v1/dolares/oficial', false, $ctx);
        if ($raw) {
            $data = json_decode($raw, true);
            if (!empty($data['promedio']) && is_numeric($data['promedio']) && (float)$data['promedio'] > 0) {
                $tasa = (float)$data['promedio'];
            }
        }
    } catch (\Throwable $e) {}

    // 3. Si la principal falló, consultar API secundaria de respaldo
    if ($tasa <= 0) {
        try {
            $ctx2 = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'header'  => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64)\r\n"
                ],
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ]
            ]);
            $raw2 = @file_get_contents('https://pydolarvenezuela-api.vercel.app/api/v1/dollar/page?page=bcv', false, $ctx2);
            if ($raw2) {
                $data2 = json_decode($raw2, true);
                if (!empty($data2['monitors']['usd']['price'])) {
                    $tasa = (float)$data2['monitors']['usd']['price'];
                }
            }
        } catch (\Throwable $e) {}
    }

    // 4. Si se obtuvo una tasa válida, guardar en caché
    if ($tasa > 0) {
        @file_put_contents($cacheFile, json_encode([
            'tasa' => $tasa,
            'actualizado_en' => time()
        ]));
        return $tasa;
    }

    // 5. Si no hay conexión externa en este instante, usar el valor en caché aunque sea antiguo
    if (file_exists($cacheFile)) {
        $cached = @json_decode((string)file_get_contents($cacheFile), true);
        if (!empty($cached['tasa']) && is_numeric($cached['tasa']) && (float)$cached['tasa'] > 0) {
            return (float)$cached['tasa'];
        }
    }

    // 6. Respaldo definitivo de emergencia
    return 875.6505;
}

/**
 * ══════════════════════════════════════════════════════════════════
 * SISTEMA DE NOTIFICACIONES BIDIRECCIONALES & ASIMÉTRICAS
 * ══════════════════════════════════════════════════════════════════
 */

/**
 * Garantiza que notificaciones.rol_destinatario acepte 'admin' (migración idempotente).
 */
function ensureAdminNotifSchema(PDO $db): void {
    static $ensured = false;
    if ($ensured) return;
    $ensured = true;
    try {
        $st = $db->query("SHOW COLUMNS FROM notificaciones LIKE 'rol_destinatario'");
        $col = $st ? $st->fetch(PDO::FETCH_ASSOC) : null;
        if ($col && isset($col['Type']) && !str_contains(strtolower((string)$col['Type']), "'admin'")) {
            $db->exec("ALTER TABLE notificaciones MODIFY COLUMN rol_destinatario ENUM('admin','entrenador','cliente') NULL DEFAULT NULL");
        }
    } catch (\Throwable $e) {
        error_log('[ensureAdminNotifSchema] ' . $e->getMessage());
    }
}

/**
 * Registra una notificación en la base de datos garantizando bidireccionalidad
 * y retrocompatibilidad total con columnas existentes y nuevas.
 */
function registrarNotificacionSistema(array $params): int|bool {
    try {
        $db = getDB();

        $remitenteId    = !empty($params['remitente_id']) ? (int)$params['remitente_id'] : null;
        $destinatarioId = !empty($params['destinatario_id']) ? (int)$params['destinatario_id'] : null;
        $rolDestinatario= $params['rol_destinatario'] ?? 'entrenador';
        if ($rolDestinatario === 'admin') {
            ensureAdminNotifSchema($db);
        }
        $tipoEvento     = $params['tipo_evento'] ?? 'sistema';
        $referenciaId   = !empty($params['referencia_id']) ? (int)$params['referencia_id'] : null;
        $clienteId      = !empty($params['cliente_id']) ? (int)$params['cliente_id'] : null;
        $entrenadorId   = !empty($params['entrenador_id']) ? (int)$params['entrenador_id'] : null;
        $mensaje        = trim($params['mensaje'] ?? '');
        $enlace         = $params['enlace'] ?? null;
        $metadatos      = is_array($params['metadatos'] ?? null) 
                            ? json_encode($params['metadatos'], JSON_UNESCAPED_UNICODE) 
                            : ($params['metadatos'] ?? null);

        if ($mensaje === '') return false;

        // Inferencia inteligente de IDs cruzados si alguno falta
        if ($clienteId && !$destinatarioId && $rolDestinatario === 'cliente') {
            $st = $db->prepare("SELECT usuario_id, entrenador_id FROM clientes WHERE id = ?");
            $st->execute([$clienteId]);
            $cRow = $st->fetch();
            if ($cRow) {
                $destinatarioId = (int)$cRow['usuario_id'];
                if (!$entrenadorId) $entrenadorId = (int)$cRow['entrenador_id'];
                if (!$remitenteId) $remitenteId = $entrenadorId;
            }
        } elseif ($clienteId && !$destinatarioId && $rolDestinatario === 'entrenador') {
            $st = $db->prepare("SELECT usuario_id, entrenador_id FROM clientes WHERE id = ?");
            $st->execute([$clienteId]);
            $cRow = $st->fetch();
            if ($cRow) {
                $destinatarioId = (int)$cRow['entrenador_id'];
                $entrenadorId   = $destinatarioId;
                if (!$remitenteId) $remitenteId = (int)$cRow['usuario_id'];
            }
        }

        if (!$clienteId && $destinatarioId && $rolDestinatario === 'cliente') {
            $st = $db->prepare("SELECT id, entrenador_id FROM clientes WHERE usuario_id = ?");
            $st->execute([$destinatarioId]);
            $cRow = $st->fetch();
            if ($cRow) {
                $clienteId = (int)$cRow['id'];
                if (!$entrenadorId) $entrenadorId = (int)$cRow['entrenador_id'];
            }
        } elseif (!$clienteId && $remitenteId) {
            $st = $db->prepare("SELECT id, entrenador_id FROM clientes WHERE usuario_id = ?");
            $st->execute([$remitenteId]);
            $cRow = $st->fetch();
            if ($cRow) {
                $clienteId = (int)$cRow['id'];
                if (!$entrenadorId) $entrenadorId = (int)$cRow['entrenador_id'];
            }
        }

        $sql = "INSERT INTO notificaciones 
                (remitente_id, destinatario_id, rol_destinatario, tipo_evento, referencia_id, 
                 cliente_id, entrenador_id, mensaje, fecha, created_at, leida, leido, tipo, enlace, metadatos) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW(), 0, 0, ?, ?, ?)";
        $stmt = $db->prepare($sql);
        $res = $stmt->execute([
            $remitenteId,
            $destinatarioId,
            $rolDestinatario,
            $tipoEvento,
            $referenciaId,
            $clienteId,
            $entrenadorId,
            $mensaje,
            $tipoEvento,
            $enlace,
            $metadatos
        ]);
        return $res ? (int)$db->lastInsertId() : false;
    } catch (\Throwable $e) {
        error_log('[registrarNotificacionSistema] Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Obtiene las notificaciones ordenadas de más reciente a más antigua para el usuario actual.
 * Garantiza aislamiento estricto por rol_destinatario (atleta solo ve atleta, entrenador solo entrenador).
 */
function obtenerNotificacionesUsuario(int $userId, string $rol, int $limit = 25, bool $soloNoLeidas = false): array {
    $db = getDB();
    purgarNotificacionesAntiguas($db);

    $unreadCond = $soloNoLeidas ? " AND (n.leido = 0 AND n.leida = 0) " : "";

    if ($rol === 'admin') {
        ensureAdminNotifSchema($db);
        $st = $db->prepare("
            SELECT n.*, u.nombre AS remitente_nombre, u.avatar_url AS remitente_avatar
            FROM notificaciones n
            LEFT JOIN usuarios u ON u.id = n.remitente_id
            WHERE n.rol_destinatario = 'admin'
              AND (n.destinatario_id = ? OR n.destinatario_id IS NULL)
              {$unreadCond}
            ORDER BY COALESCE(n.created_at, n.fecha) DESC, n.id DESC
            LIMIT ?
        ");
        $st->bindValue(1, $userId, PDO::PARAM_INT);
        $st->bindValue(2, $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    } elseif ($rol === 'entrenador') {
        $st = $db->prepare("
            SELECT n.*, u.nombre AS remitente_nombre, u.avatar_url AS remitente_avatar
            FROM notificaciones n
            LEFT JOIN clientes c ON n.cliente_id = c.id
            LEFT JOIN usuarios u ON (u.id = n.remitente_id OR u.id = c.usuario_id)
            WHERE n.rol_destinatario = 'entrenador'
              AND (n.destinatario_id = ? OR (n.destinatario_id IS NULL AND n.entrenador_id = ?))
              AND n.mensaje NOT LIKE '¡Tu cuenta ha sido activada%'
              AND n.mensaje NOT LIKE 'Tu entrenador%'
              AND COALESCE(n.tipo, '') NOT IN ('rutina_asignada', 'cambios_aprobados', 'modificacion_rechazada', 'medidas_actualizadas', 'pago_aprobado')
              AND COALESCE(n.tipo_evento, '') NOT IN ('rutina_asignada', 'cambios_aprobados', 'modificacion_rechazada', 'medidas_actualizadas', 'pago_aprobado')
              {$unreadCond}
            ORDER BY COALESCE(n.created_at, n.fecha) DESC, n.id DESC
            LIMIT ?
        ");
        $st->bindValue(1, $userId, PDO::PARAM_INT);
        $st->bindValue(2, $userId, PDO::PARAM_INT);
        $st->bindValue(3, $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Rol cliente: buscar estrictamente notificaciones dirigidas a rol cliente
        $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
        $st = $db->prepare("
            SELECT n.*, u.nombre AS remitente_nombre, u.avatar_url AS remitente_avatar
            FROM notificaciones n
            LEFT JOIN usuarios u ON (u.id = n.remitente_id OR u.id = n.entrenador_id)
            WHERE n.rol_destinatario = 'cliente'
              AND (n.destinatario_id = ? OR (n.destinatario_id IS NULL AND n.cliente_id = ?))
              AND n.mensaje NOT LIKE 'Nuevo atleta%'
              AND n.mensaje NOT LIKE '%solicitó una modificación%'
              AND n.mensaje NOT LIKE '%reportó un pago%'
              AND COALESCE(n.tipo, '') NOT IN ('registro', 'solicitud_atleta', 'solicitud_modificacion', 'modificacion_directa', 'pago_reportado', 'rutina_completada')
              AND COALESCE(n.tipo_evento, '') NOT IN ('registro', 'solicitud_atleta', 'solicitud_modificacion', 'modificacion_directa', 'pago_reportado', 'rutina_completada')
              {$unreadCond}
            ORDER BY COALESCE(n.created_at, n.fecha) DESC, n.id DESC
            LIMIT ?
        ");
        $st->bindValue(1, $userId, PDO::PARAM_INT);
        $st->bindValue(2, $clienteId, PDO::PARAM_INT);
        $st->bindValue(3, $limit, PDO::PARAM_INT);
        $st->execute();
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
    }

    $hoyYmd  = date('Y-m-d');
    $ayerYmd = date('Y-m-d', strtotime('-1 day'));
    $semYmd  = date('Y-m-d', strtotime('-7 days'));

    foreach ($rows as &$r) {
        $rawDate = $r['created_at'] ?? $r['fecha'] ?? '';
        $ts      = $rawDate ? strtotime($rawDate) : time();
        $ymd     = date('Y-m-d', $ts);

        $r['tiempo_relativo']  = formatoTiempoRelativo($rawDate);
        $r['fecha_formateada'] = date('d/m/Y', $ts);
        $r['hora_formateada']  = date('h:i A', $ts);
        if ($ymd === $hoyYmd) {
            $r['grupo_fecha'] = 'Hoy';
        } elseif ($ymd === $ayerYmd) {
            $r['grupo_fecha'] = 'Ayer';
        } elseif ($ymd >= $semYmd) {
            $r['grupo_fecha'] = 'Esta semana';
        } else {
            $r['grupo_fecha'] = 'Anteriores';
        }

        $r['icono'] = iconoPorTipoEvento($r['tipo_evento'] ?? $r['tipo'] ?? '', $r['mensaje'] ?? '');
        $r['leido'] = (int)($r['leido'] ?? $r['leida'] ?? 0);
    }
    unset($r);

    return $rows;
}

/**
 * Cuenta las notificaciones pendientes de lectura para el usuario en sesión (aislado por rol).
 */
function contarNotificacionesNoLeidas(int $userId, string $rol): int {
    $db = getDB();
    purgarNotificacionesAntiguas($db);
    if ($rol === 'admin') {
        ensureAdminNotifSchema($db);
        $st = $db->prepare("
            SELECT COUNT(*) FROM notificaciones n
            WHERE n.rol_destinatario = 'admin'
              AND (n.destinatario_id = ? OR n.destinatario_id IS NULL)
              AND (n.leido = 0 AND n.leida = 0)
        ");
        $st->execute([$userId]);
        return (int)$st->fetchColumn();
    } elseif ($rol === 'entrenador') {
        $st = $db->prepare("
            SELECT COUNT(*) FROM notificaciones n
            WHERE n.rol_destinatario = 'entrenador'
              AND (n.destinatario_id = ? OR (n.destinatario_id IS NULL AND n.entrenador_id = ?))
              AND n.mensaje NOT LIKE '¡Tu cuenta ha sido activada%'
              AND n.mensaje NOT LIKE 'Tu entrenador%'
              AND COALESCE(n.tipo, '') NOT IN ('rutina_asignada', 'cambios_aprobados', 'modificacion_rechazada', 'medidas_actualizadas', 'pago_aprobado')
              AND COALESCE(n.tipo_evento, '') NOT IN ('rutina_asignada', 'cambios_aprobados', 'modificacion_rechazada', 'medidas_actualizadas', 'pago_aprobado')
              AND (n.leido = 0 AND n.leida = 0)
        ");
        $st->execute([$userId, $userId]);
        return (int)$st->fetchColumn();
    } else {
        $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
        $st = $db->prepare("
            SELECT COUNT(*) FROM notificaciones n
            WHERE n.rol_destinatario = 'cliente'
              AND (n.destinatario_id = ? OR (n.destinatario_id IS NULL AND n.cliente_id = ?))
              AND n.mensaje NOT LIKE 'Nuevo atleta%'
              AND n.mensaje NOT LIKE '%solicitó una modificación%'
              AND n.mensaje NOT LIKE '%reportó un pago%'
              AND COALESCE(n.tipo, '') NOT IN ('registro', 'solicitud_atleta', 'solicitud_modificacion', 'modificacion_directa', 'pago_reportado', 'rutina_completada')
              AND COALESCE(n.tipo_evento, '') NOT IN ('registro', 'solicitud_atleta', 'solicitud_modificacion', 'modificacion_directa', 'pago_reportado', 'rutina_completada')
              AND (n.leido = 0 AND n.leida = 0)
        ");
        $st->execute([$userId, $clienteId]);
        return (int)$st->fetchColumn();
    }
}

/**
 * Marca una notificación individual como leída garantizando ownership y aislamiento por rol.
 */
function marcarNotificacionLeida(int $notifId, int $userId): bool {
    $db = getDB();
    $rol = $_SESSION['rol'] ?? 'cliente';
    $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
    if ($rol === 'admin') {
        ensureAdminNotifSchema($db);
        $st = $db->prepare("
            UPDATE notificaciones
            SET leido = 1, leida = 1
            WHERE id = ? AND rol_destinatario = 'admin'
              AND (destinatario_id = ? OR destinatario_id IS NULL)
        ");
        return $st->execute([$notifId, $userId]);
    } elseif ($rol === 'entrenador') {
        $st = $db->prepare("
            UPDATE notificaciones 
            SET leido = 1, leida = 1 
            WHERE id = ? AND rol_destinatario = 'entrenador'
              AND (destinatario_id = ? OR entrenador_id = ?)
        ");
        return $st->execute([$notifId, $userId, $userId]);
    } else {
        $st = $db->prepare("
            UPDATE notificaciones 
            SET leido = 1, leida = 1 
            WHERE id = ? AND rol_destinatario = 'cliente'
              AND (destinatario_id = ? OR cliente_id = ?)
        ");
        return $st->execute([$notifId, $userId, $clienteId]);
    }
}

/**
 * Marca todas las notificaciones del usuario como leídas de forma idempotente y aislada por rol.
 */
function marcarTodasNotificacionesLeidas(int $userId, string $rol): bool {
    $db = getDB();
    if ($rol === 'admin') {
        ensureAdminNotifSchema($db);
        $st = $db->prepare("
            UPDATE notificaciones
            SET leido = 1, leida = 1
            WHERE rol_destinatario = 'admin'
              AND (destinatario_id = ? OR destinatario_id IS NULL)
        ");
        return $st->execute([$userId]);
    } elseif ($rol === 'entrenador') {
        $st = $db->prepare("
            UPDATE notificaciones 
            SET leido = 1, leida = 1 
            WHERE rol_destinatario = 'entrenador'
              AND (destinatario_id = ? OR (destinatario_id IS NULL AND entrenador_id = ?))
        ");
        return $st->execute([$userId, $userId]);
    } else {
        $clienteId = (int)($_SESSION['cliente_id'] ?? 0);
        $st = $db->prepare("
            UPDATE notificaciones 
            SET leido = 1, leida = 1 
            WHERE rol_destinatario = 'cliente'
              AND (destinatario_id = ? OR (destinatario_id IS NULL AND cliente_id = ?))
        ");
        return $st->execute([$userId, $clienteId]);
    }
}

/**
 * Higiene y persistencia: Normaliza rol_destinatario en registros antiguos y purga leídas > 30 días.
 */
function purgarNotificacionesAntiguas(PDO $db): void {
    // Ejecución silenciosa y ligera
    static $purgadoEnEsteCiclo = false;
    if ($purgadoEnEsteCiclo) return;
    $purgadoEnEsteCiclo = true;

    try {
        // 1. Reclasificar notificaciones de atleta que quedaron con rol_destinatario = 'entrenador' por DEFAULT de columna
        $db->exec("
            UPDATE notificaciones n
            LEFT JOIN clientes c ON n.cliente_id = c.id
            SET n.rol_destinatario = 'cliente',
                n.destinatario_id = COALESCE(c.usuario_id, n.destinatario_id)
            WHERE (
                n.mensaje LIKE '¡Tu cuenta ha sido activada%'
                OR n.mensaje LIKE 'Tu entrenador%'
                OR n.mensaje LIKE '%ha sido aprobada%'
                OR n.mensaje LIKE '%fue rechazada%'
                OR n.mensaje LIKE '%actualizó tus medidas%'
                OR n.tipo IN ('rutina_asignada', 'cambios_aprobados', 'modificacion_rechazada', 'medidas_actualizadas', 'pago_aprobado')
                OR n.tipo_evento IN ('rutina_asignada', 'cambios_aprobados', 'modificacion_rechazada', 'medidas_actualizadas', 'pago_aprobado')
            ) AND (n.rol_destinatario IS NULL OR n.rol_destinatario NOT IN ('cliente', 'admin'))
        ");

        // 2. Normalizar registros históricos sin rol_destinatario para evitar cruce entre atleta y entrenador
        $db->exec("
            UPDATE notificaciones
            SET rol_destinatario = CASE
                WHEN tipo IN ('registro', 'solicitud_atleta', 'solicitud_modificacion', 'modificacion_directa', 'pago_reportado', 'rutina_completada')
                  OR tipo_evento IN ('registro', 'solicitud_atleta', 'solicitud_modificacion', 'modificacion_directa', 'pago_reportado', 'rutina_completada')
                  OR mensaje LIKE 'Nuevo atleta%'
                  OR mensaje LIKE '%solicitó%'
                  OR mensaje LIKE '%modificó%'
                  OR mensaje LIKE '%completó%'
                  OR mensaje LIKE '%reportó un pago%'
                THEN 'entrenador'
                ELSE 'cliente'
            END
            WHERE rol_destinatario IS NULL OR rol_destinatario = ''
        ");

        // 2. Purgar leídas con más de 30 días
        $db->exec("
            DELETE FROM notificaciones 
            WHERE (leido = 1 OR leida = 1) 
              AND (created_at < DATE_SUB(NOW(), INTERVAL 30 DAY) OR fecha < DATE_SUB(NOW(), INTERVAL 30 DAY))
            LIMIT 50
        ");
    } catch (\Throwable $e) {
        // Fail-safe silencioso
    }
}

/**
 * Formato de tiempo relativo amigable (UI-UX Pro-Max)
 */
function formatoTiempoRelativo(string $fechaStr): string {
    if (!$fechaStr || $fechaStr === '0000-00-00 00:00:00') return '';
    $ts = strtotime($fechaStr);
    if (!$ts) return '';
    
    $diff = time() - $ts;
    if ($diff < 45) return 'Hace instantes';
    if ($diff < 3600) {
        $mins = max(1, (int)floor($diff / 60));
        return "Hace {$mins} min";
    }
    if ($diff < 86400) {
        $horas = (int)floor($diff / 3600);
        return $horas === 1 ? 'Hace 1 hora' : "Hace {$horas} horas";
    }
    if ($diff < 172800) {
        return 'Ayer ' . date('H:i', $ts);
    }
    if ($diff < 604800) {
        $dias = (int)floor($diff / 86400);
        return "Hace {$dias} días";
    }
    return date('d/m/Y', $ts);
}

/**
 * Retorna el icono representativo de Tabler Icons según el evento
 */
function iconoPorTipoEvento(string $tipoEvento, string $mensaje = ''): string {
    $ev = strtolower($tipoEvento);
    $msg = strtolower($mensaje);

    if (str_contains($ev, 'cambios_aprobados') || str_contains($ev, 'verificado')) {
        return 'ti-circle-check';
    }
    if (str_contains($ev, 'edicion_rutina') || str_contains($ev, 'modific') || str_contains($msg, 'cambio en rutina')) {
        return 'ti-exchange';
    }
    if (str_contains($ev, 'entrenamiento_completado') || str_contains($msg, 'completó su entrenamiento')) {
        return 'ti-trophy';
    }
    if (str_contains($ev, 'rutina_asignada') || str_contains($ev, 'rutina') || str_contains($msg, 'rutina')) {
        return 'ti-barbell';
    }
    if (str_contains($ev, 'pago') || str_contains($msg, 'pago') || str_contains($msg, 'mensualidad')) {
        return 'ti-credit-card';
    }
    if (str_contains($ev, 'solicitud_atleta') || str_contains($ev, 'solicitud_entrenador') || str_contains($ev, 'registro') || str_contains($msg, 'espera')) {
        return 'ti-user-plus';
    }
    if (str_contains($ev, 'activacion_cuenta') || str_contains($msg, 'activada')) {
        return 'ti-shield-check';
    }
    if (str_contains($ev, 'medidas') || str_contains($msg, 'medidas')) {
        return 'ti-ruler-measure';
    }
    return 'ti-bell';
}

/**
 * Sincroniza atómicamente la sesión de asistencia diaria del atleta en sesiones_asistencia.
 * Garantiza integridad de datos: entrenador_id correcto, verificación y estados iniciada/completada.
 */
function sincronizarSesionAsistencia(PDO $db, int $clienteId, int $rutinaId, string $estado = 'iniciada', bool $completar = false, ?string $fecha = null): int {
    // 1. Sanitizar fecha (respetar la fecha real del entrenamiento si vino de sincronización offline)
    $hoy = date('Y-m-d');
    $fechaReal = $hoy;
    if ($fecha && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        if ($fecha <= $hoy && $fecha >= date('Y-m-d', strtotime('-14 days'))) {
            $fechaReal = $fecha;
        }
    }

    // 2. Obtener entrenador del cliente o de la rutina
    $stEnt = $db->prepare("
        SELECT COALESCE(r.entrenador_id, c.entrenador_id, 1) AS entrenador_id 
        FROM clientes c
        LEFT JOIN rutinas r ON r.id = ?
        WHERE c.id = ? LIMIT 1
    ");
    $stEnt->execute([$rutinaId, $clienteId]);
    $entrenadorId = (int)($stEnt->fetchColumn() ?: 1);

    // 3. Buscar si ya existe una sesión para la fecha dada
    $stExist = $db->prepare("
        SELECT id, estado FROM sesiones_asistencia 
        WHERE cliente_id = ? AND rutina_id = ? AND DATE(inicio_en) = ? 
        ORDER BY id DESC LIMIT 1
    ");
    $stExist->execute([$clienteId, $rutinaId, $fechaReal]);
    $existente = $stExist->fetch(PDO::FETCH_ASSOC);

    $horaFin = ($fechaReal === $hoy) ? date('Y-m-d H:i:s') : "{$fechaReal} 20:00:00";
    $horaInicio = ($fechaReal === $hoy) ? date('Y-m-d H:i:s') : "{$fechaReal} 18:30:00";

    $estadoFinal = ($completar || $estado === 'completada') ? 'completada' : ($estado === 'incompleta' ? 'incompleta' : 'iniciada');

    if ($existente) {
        $sesId = (int)$existente['id'];
        if ($estadoFinal === 'completada' || $estadoFinal === 'incompleta') {
            $db->prepare("
                UPDATE sesiones_asistencia 
                SET estado = ?, fin_en = ? 
                WHERE id = ?
            ")->execute([$estadoFinal, $horaFin, $sesId]);
        }
        return $sesId;
    } else {
        $finValor = ($estadoFinal === 'completada' || $estadoFinal === 'incompleta') ? $horaFin : null;
        $stIns = $db->prepare("
            INSERT INTO sesiones_asistencia (rutina_id, cliente_id, entrenador_id, estado, inicio_en, fin_en, verificada)
            VALUES (?, ?, ?, ?, ?, ?, 0)
        ");
        $stIns->execute([$rutinaId, $clienteId, $entrenadorId, $estadoFinal, $horaInicio, $finValor]);
        return (int)$db->lastInsertId();
    }
}

/**
 * Resuelve la paleta visual efectiva para el usuario actual y el estado global del tema.
 * - `tema_sistema`: paleta global para toda la web.
 * - `tema_global_ver`: versión incremental cada vez que el Super Admin guarda un tema global.
 * - `tema_usuario_{uid}`: preferencia privada `{tema}|{globalVer}` del usuario (entrenador o superadmin).
 */
function xpResolverTemaWeb(PDO $db, ?int $userId = null): array {
    $validos = ['classic', 'obsidian', 'royal-plum', 'deep-ocean', 'nordic-frost', 'dark', 'light'];
    $temaGlobal = 'classic';
    $globalVer  = 1;
    $alcance    = 'personal';
    $userRaw    = '';

    try {
        $claves = ['tema_sistema', 'tema_global_ver', 'tema_superadmin_alcance'];
        if ($userId !== null && $userId > 0) {
            $claves[] = 'tema_usuario_' . $userId;
        }
        $ph = implode(',', array_fill(0, count($claves), '?'));
        $st = $db->prepare("SELECT clave, valor FROM ajustes_globales WHERE clave IN ($ph)");
        $st->execute($claves);
        $kv = [];
        while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
            $kv[$row['clave']] = (string)($row['valor'] ?? '');
        }
        if (!empty($kv['tema_sistema']) && in_array(trim($kv['tema_sistema']), $validos, true)) {
            $temaGlobal = trim($kv['tema_sistema']);
        }
        if (!empty($kv['tema_global_ver']) && is_numeric($kv['tema_global_ver'])) {
            $globalVer = max(1, (int)$kv['tema_global_ver']);
        }
        if (($kv['tema_superadmin_alcance'] ?? '') === 'global') {
            $alcance = 'global';
        }
        if ($userId !== null && $userId > 0) {
            $userRaw = trim((string)($kv['tema_usuario_' . $userId] ?? ''));
        }
    } catch (\Throwable $e) {
        error_log('[xpResolverTemaWeb] ' . $e->getMessage());
    }

    $temaEfectivo = $temaGlobal;
    if ($userRaw !== '') {
        $partes = explode('|', $userRaw, 2);
        $uTema  = trim($partes[0] ?? '');
        $uVer   = isset($partes[1]) && is_numeric($partes[1]) ? (int)$partes[1] : $globalVer;
        if (in_array($uTema, $validos, true) && $uVer >= $globalVer) {
            $temaEfectivo = $uTema;
        }
    }

    return [
        'tema'          => $temaEfectivo,
        'tema_global'   => $temaGlobal,
        'global_ver'    => $globalVer,
        'global_activo' => ($alcance === 'global'),
    ];
}

// Cargar lógica de progreso y estadísticas de ejercicios
require_once __DIR__ . '/progreso.php';


