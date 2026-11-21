<?php
// controllers/GoogleAuthController.php — Google OAuth 2.0
// Sin librerías externas. Implementación manual con curl y JWT decode básico.
declare(strict_types=1);

/**
 * Obtener la URI de redirección de OAuth 2.0 garantizando coincidencia exacta con Google Console.
 */
function getGoogleRedirectUri(): string {
    $envUri = trim((string)env('GOOGLE_REDIRECT_URI', ''));
    if ($envUri !== '') {
        return rtrim($envUri, '/');
    }
    $base = rtrim(str_replace(' ', '%20', trim((string)APP_URL)), '/');
    return $base . '/auth/google-callback';
}

function googleOAuthIsHttps(): bool {
    return (
        (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ||
        (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') ||
        (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower((string)$_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') ||
        (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
    );
}

/**
 * Genera un token `state` firmado criptográficamente (HMAC-SHA256 + timestamp)
 * resiliente a reinicios de contenedor y cambios de contexto en PWA instalada.
 */
function googleBuildSignedState(): string {
    $nonce  = bin2hex(random_bytes(16));
    $ts     = (string)time();
    $secret = function_exists('xpRememberSecret') ? xpRememberSecret() : (string)env('GOOGLE_CLIENT_SECRET', 'atlas-oauth');
    $sig    = hash_hmac('sha256', $nonce . '|' . $ts, $secret);
    return $nonce . '.' . $ts . '.' . $sig;
}

function googleVerifySignedState(string $state, int $maxAgeSeconds = 600): bool {
    $parts = explode('.', $state);
    if (count($parts) !== 3) return false;
    [$nonce, $tsStr, $sig] = $parts;
    if (!preg_match('/^[a-f0-9]{32}$/', $nonce) || !ctype_digit($tsStr) || strlen($sig) !== 64) {
        return false;
    }
    $ts = (int)$tsStr;
    $now = time();
    if ($ts > ($now + 60) || ($now - $ts) > $maxAgeSeconds) {
        return false;
    }
    $secret   = function_exists('xpRememberSecret') ? xpRememberSecret() : (string)env('GOOGLE_CLIENT_SECRET', 'atlas-oauth');
    $expected = hash_hmac('sha256', $nonce . '|' . $tsStr, $secret);
    return hash_equals($expected, $sig);
}

/**
 * PASO 1: Redirigir al usuario a Google para autenticación.
 * Ruta: GET /auth/google
 */
function googleRedirect(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $clientId    = trim((string)env('GOOGLE_CLIENT_ID', ''));
    $redirectUri = getGoogleRedirectUri();

    if (empty($clientId) || $clientId === 'PENDIENTE_CONFIGURAR') {
        setFlash('error', 'Google OAuth no está configurado aún. Usa el login tradicional.');
        redirect('auth/login');
    }

    // ── Generar state CSRF firmado y persistirlo en sesión + cookie SameSite=Lax ──
    $state = googleBuildSignedState();
    $_SESSION['oauth_state']        = $state;
    $_SESSION['oauth_state_time']   = time();
    $_SESSION['oauth_redirect_uri'] = $redirectUri;

    @setcookie('xp_oauth_state', $state, [
        'expires'  => time() + 600,
        'path'     => '/',
        'secure'   => googleOAuthIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @setcookie('xp_oauth_redir', $redirectUri, [
        'expires'  => time() + 600,
        'path'     => '/',
        'secure'   => googleOAuthIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Garantizar que la sesión se escriba a disco antes de seguir la redirección externa
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }

    $params = http_build_query([
        'client_id'     => $clientId,
        'redirect_uri'  => $redirectUri,
        'response_type' => 'code',
        'scope'         => 'openid email profile',
        'state'         => $state,
        'access_type'   => 'online',
        'prompt'        => 'select_account', // Forzar selección de cuenta
    ], '', '&', PHP_QUERY_RFC3986);

    header('Location: https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    exit;
}

/**
 * PASO 2: Procesar el callback de Google.
 * Ruta: GET /auth/google-callback
 */
function googleCallback(): void {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    // ── Validar state CSRF (sesión, cookie dedicada o firma HMAC si la PWA cambió de contexto) ──
    $state         = trim((string)($_GET['state'] ?? ''));
    $expectedState = (string)($_SESSION['oauth_state'] ?? '');
    $stateTime     = (int)($_SESSION['oauth_state_time'] ?? 0);
    $cookieState   = (string)($_COOKIE['xp_oauth_state'] ?? '');
    $savedRedirUri = trim((string)($_SESSION['oauth_redirect_uri'] ?? ($_COOKIE['xp_oauth_redir'] ?? '')));

    // Si el usuario ya tiene sesión activa (ej. callback duplicado o reintento en navegador móvil/PWA), redirigir directamente sin mostrar error invalid_grant
    if (!empty($_SESSION['user_id']) && !empty($_SESSION['rol'])) {
        unset($_SESSION['flash'], $_SESSION['oauth_state'], $_SESSION['oauth_state_time'], $_SESSION['oauth_redirect_uri']);
        $destAct = ($_SESSION['rol'] ?? '') === 'admin'
            ? 'admin/rutina'
            : (($_SESSION['rol'] ?? '') === 'entrenador' ? 'entrenador/dashboard' : 'cliente/dashboard');
        redirect($destAct);
    }

    // Limpiar state de sesión y cookies
    unset($_SESSION['oauth_state'], $_SESSION['oauth_state_time'], $_SESSION['oauth_redirect_uri']);
    @setcookie('xp_oauth_state', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => googleOAuthIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    @setcookie('xp_oauth_redir', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => googleOAuthIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    $stateValid = false;
    if ($state !== '') {
        if ($expectedState !== '' && hash_equals($expectedState, $state) && (time() - $stateTime) <= 600) {
            $stateValid = true;
        } elseif ($cookieState !== '' && hash_equals($cookieState, $state) && googleVerifySignedState($state, 600)) {
            $stateValid = true;
        }
    }

    if (!$stateValid) {
        error_log('[Xprod Fit][OAuth] State CSRF inválido o expirado (hasSession=' . ($expectedState !== '' ? '1' : '0') . ', hasCookie=' . ($cookieState !== '' ? '1' : '0') . ')');
        setFlash('error', 'La sesión de inicio con Google expiró o cambió de ventana. Intenta iniciar sesión nuevamente.');
        redirect('auth/login');
    }

    // ── Verificar que no haya error de Google ──
    if (!empty($_GET['error'])) {
        $error = htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8');
        error_log("[Xprod Fit][OAuth] Error de Google: {$error}");
        setFlash('error', 'No se pudo autenticar con Google. Intenta de nuevo.');
        redirect('auth/login');
    }

    $code = $_GET['code'] ?? '';
    if (empty($code)) {
        setFlash('error', 'Código de autorización no recibido.');
        redirect('auth/login');
    }

    try {
        // ── Paso A: Intercambiar code por tokens usando exactamente el redirect_uri de Paso 1 ──
        $tokens = googleExchangeCode($code, $savedRedirUri);

        if (empty($tokens['id_token'])) {
            throw new \RuntimeException('No se recibió id_token de Google.');
        }

        // ── Paso B: Decodificar y verificar id_token ──
        $payload = googleDecodeIdToken($tokens['id_token']);

        if (empty($payload['email']) || empty($payload['sub'])) {
            throw new \RuntimeException('Datos de usuario incompletos en el token de Google.');
        }

        // Verificar que el email esté verificado por Google
        if (empty($payload['email_verified'])) {
            throw new \RuntimeException('El email de Google no está verificado.');
        }

        // ── Paso C: Evaluar si el usuario existe y si su perfil está completo ──
        $email      = strtolower(trim((string)$payload['email']));
        $googleId   = (string)$payload['sub'];
        $givenName  = trim((string)($payload['given_name'] ?? ''));
        $familyName = trim((string)($payload['family_name'] ?? ''));
        $avatarUrl  = (string)($payload['picture'] ?? '');

        $um = new UsuarioModel();
        $db = getDB();

        $existingUser = $um->findByGoogleId($googleId) ?: $um->findByEmail($email);
        $cliente = null;
        if ($existingUser) {
            $stCl = $db->prepare("SELECT id, cedula, peso_kg, altura_cm, objetivo, fecha_nac FROM clientes WHERE usuario_id = ? LIMIT 1");
            $stCl->execute([(int)$existingUser['id']]);
            $cliente = $stCl->fetch(\PDO::FETCH_ASSOC);
        }

        // Evaluar perfil incompleto: si no existe o falta peso_kg, altura_cm u objetivo (los admins y entrenadores no requieren perfil de atleta)
        $esStaff = ($existingUser && in_array($existingUser['rol'] ?? '', ['admin', 'entrenador'], true));
        $perfilIncompleto = !$esStaff && (!$existingUser || !$cliente || empty($cliente['peso_kg']) || empty($cliente['altura_cm']) || empty($cliente['objetivo']));

        if ($perfilIncompleto) {
            // Usuario Nuevo o Perfil Incompleto: asegurar registro en usuarios y clientes con estado_cuenta = 'pendiente'
            $uid = $um->createOrUpdateFromGoogle($payload);

            // Asegurar asignación en clientes (respetando código de referido activo si existe)
            $stCheckCli = $db->prepare("SELECT id, cedula FROM clientes WHERE usuario_id = ? LIMIT 1");
            $stCheckCli->execute([$uid]);
            $clRow = $stCheckCli->fetch(\PDO::FETCH_ASSOC);
            if (!$clRow) {
                $refCodeOAuth = (string)($_SESSION['ref_codigo'] ?? ($_COOKIE['xp_ref_code'] ?? ''));
                $refEntOAuth  = $refCodeOAuth !== '' ? resolverEntrenadorPorCodigo($refCodeOAuth) : null;
                $tr = $refEntOAuth ? (int)$refEntOAuth['entrenador_id'] : null;
                $db->prepare("INSERT INTO clientes (usuario_id, entrenador_id) VALUES (?, ?)")
                   ->execute([$uid, $tr]);
            }

            // Pre-cargar nombre, apellido, correo y cédula (si existe) para el Onboarding Wizard
            $intentRole = (string)($_SESSION['oauth_intent_role'] ?? ($_COOKIE['xp_oauth_role'] ?? ''));
            unset($_SESSION['oauth_intent_role']);
            $_SESSION['google_onboarding'] = [
                'user_id'       => $uid,
                'email'         => $email,
                'nombres'       => $givenName ?: ($payload['name'] ?? ''),
                'apellidos'     => $familyName,
                'avatar_url'    => $avatarUrl,
                'cedula'        => $clRow['cedula'] ?? ($cliente['cedula'] ?? ''),
                'es_entrenador' => ($intentRole === 'entrenador'),
            ];

            // NO permitir acceso al Dashboard: destruir variables de sesión de usuario activo
            unset($_SESSION['user_id'], $_SESSION['nombre'], $_SESSION['email'], $_SESSION['rol'], $_SESSION['_login_at'], $_SESSION['_via']);
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Redirigir al Onboarding Wizard en Modo Google
            redirect('auth/registro?google=1');
            return;
        }

        // ── Paso D: Usuario con perfil completo ──
        $user = $um->findById((int)$existingUser['id']);
        if (!$user) throw new \RuntimeException('Usuario no encontrado tras OAuth.');

        // Bloquear cuentas desactivadas (incluyendo admin)
        if (empty($user['activo'])) {
            unset($_SESSION['user_id'], $_SESSION['nombre'], $_SESSION['email'], $_SESSION['rol'], $_SESSION['_login_at'], $_SESSION['_via']);
            setFlash('error', 'Tu cuenta ha sido desactivada. Contacta al administrador.');
            if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
            redirect('auth/login');
            return;
        }

        // Si el estado de cuenta sigue en 'pendiente' o 'rechazado', bloquear y mostrar vista Stand-by (solo admin exento)
        if (($user['rol'] ?? '') !== 'admin') {
            $esEnt = (($user['rol'] ?? '') === 'entrenador');
            if (($user['estado_cuenta'] ?? 'pendiente') === 'pendiente') {
                unset($_SESSION['user_id'], $_SESSION['nombre'], $_SESSION['email'], $_SESSION['rol'], $_SESSION['_login_at'], $_SESSION['_via']);
                if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
                redirect('auth/login?standby=1' . ($esEnt ? '&rol=entrenador' : ''));
                return;
            } elseif (($user['estado_cuenta'] ?? '') === 'rechazado') {
                unset($_SESSION['user_id'], $_SESSION['nombre'], $_SESSION['email'], $_SESSION['rol'], $_SESSION['_login_at'], $_SESSION['_via']);
                setFlash('error', $esEnt ? UsuarioModel::MSG_RECHAZADO_ENTRENADOR : UsuarioModel::MSG_RECHAZADO);
                if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
                redirect('auth/login');
                return;
            }
        }

        // ── Paso E: Establecer sesión (Usuario activo) ──
        session_regenerate_id(true);
        unset($_SESSION['flash']);
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['nombre']     = $user['nombre'];
        $_SESSION['email']      = $user['email'];
        $_SESSION['rol']        = ($user['rol'] === 'atleta') ? 'cliente' : $user['rol'];
        $_SESSION['_login_at']  = time();
        $_SESSION['_acc_ok_ts'] = time();
        $_SESSION['_via']       = 'google';

        if ($_SESSION['rol'] === 'cliente' && !empty($cliente['id'])) {
            $_SESSION['cliente_id'] = (int)$cliente['id'];
        }

        // Persistir sesión por 90 días en el teléfono (usa $user con password_hash real)
        xpSetRememberCookie($user);

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $dest = $_SESSION['rol'] === 'admin'
            ? 'admin/rutina'
            : ($_SESSION['rol'] === 'entrenador' ? 'entrenador/dashboard' : 'cliente/dashboard');
        redirect($dest);
        return;

    } catch (\Throwable $e) {
        $errTxt = $e->getMessage();
        error_log('[Xprod Fit][OAuth] Error en callback: ' . $errTxt . ' | redir=' . ($savedRedirUri !== '' ? $savedRedirUri : getGoogleRedirectUri()) . ' | host=' . ($_SERVER['HTTP_HOST'] ?? ''));
        $safeCode = '';
        if (preg_match('/\b(invalid_grant|redirect_uri_mismatch|invalid_client|unauthorized_client|invalid_request)\b/i', $errTxt, $mErr)) {
            $safeCode = ' (' . strtolower($mErr[1]) . ')';
        }
        setFlash('error', 'Error al iniciar sesión con Google' . $safeCode . '. Intenta de nuevo o usa el login tradicional.');
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        redirect('auth/login');
    }
}

/**
 * Realiza una petición HTTP POST segura compatible con cURL y con fallback nativo file_get_contents.
 * Garantiza funcionamiento incluso si la extensión php_curl no está cargada en el servidor Apache/PHP.
 */
function googleHttpPost(string $url, array $params): array {
    $postData = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $caBundle = function_exists('xpResolveCaBundle') ? xpResolveCaBundle() : null;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $curlOpts = [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
        ];
        if ($caBundle !== null) {
            $curlOpts[CURLOPT_CAINFO] = $caBundle;
        }
        curl_setopt_array($ch, $curlOpts);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException("Error cURL: {$curlError}");
        }
    } else {
        // Fallback nativo 100% PHP con stream_context (TLS estricto)
        $sslOpts = [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ];
        if ($caBundle !== null) {
            $sslOpts['cafile'] = $caBundle;
        }
        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/x-www-form-urlencoded\r\nConnection: close\r\n",
                'content'       => $postData,
                'timeout'       => 15,
                'ignore_errors' => true,
            ],
            'ssl' => $sslOpts,
        ]);

        $response = @file_get_contents($url, false, $context);
        $httpCode = 200;
        $headers  = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?? []) : ($http_response_header ?? []);
        if (!empty($headers) && is_array($headers)) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0] ?? '', $m)) {
                $httpCode = (int)$m[1];
            }
        }
        if ($response === false) {
            throw new \RuntimeException('Error de conexión HTTP nativo al contactar con Google.');
        }
    }

    $data = json_decode((string)$response, true);

    if ($httpCode !== 200 || empty($data)) {
        $errCode = is_array($data) ? trim((string)($data['error'] ?? '')) : '';
        $errDesc = is_array($data) ? trim((string)($data['error_description'] ?? '')) : '';
        $detail  = trim($errCode . ($errDesc !== '' ? (': ' . $errDesc) : ''));
        if ($detail === '') {
            $detail = "Código HTTP {$httpCode}";
        }
        throw new \RuntimeException("Error al intercambiar código [HTTP {$httpCode}]: {$detail}");
    }

    return $data;
}

/**
 * Realiza una petición HTTP GET segura compatible con cURL y con fallback nativo file_get_contents.
 */
function googleHttpGet(string $url): array {
    $caBundle = function_exists('xpResolveCaBundle') ? xpResolveCaBundle() : null;
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $curlOpts = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ];
        if ($caBundle !== null) {
            $curlOpts[CURLOPT_CAINFO] = $caBundle;
        }
        curl_setopt_array($ch, $curlOpts);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
    } else {
        $sslOpts = [
            'verify_peer'      => true,
            'verify_peer_name' => true,
        ];
        if ($caBundle !== null) {
            $sslOpts['cafile'] = $caBundle;
        }
        $context = stream_context_create([
            'http' => [
                'method'        => 'GET',
                'header'        => "Connection: close\r\n",
                'timeout'       => 10,
                'ignore_errors' => true,
            ],
            'ssl' => $sslOpts,
        ]);

        $response = @file_get_contents($url, false, $context);
        $httpCode = 200;
        $headers  = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?? []) : ($http_response_header ?? []);
        if (!empty($headers) && is_array($headers)) {
            if (preg_match('/HTTP\/\d\.\d\s+(\d+)/', $headers[0] ?? '', $m)) {
                $httpCode = (int)$m[1];
            }
        }
    }

    if ($httpCode !== 200 || $response === false) {
        throw new \RuntimeException("Token de Google inválido o expirado (HTTP {$httpCode}).");
    }

    $payload = json_decode((string)$response, true);

    if (empty($payload) || !empty($payload['error'])) {
        throw new \RuntimeException('Error al validar token: ' . ($payload['error'] ?? 'desconocido'));
    }

    return $payload;
}

/**
 * Intercambia el código de autorización por tokens de acceso.
 */
function googleExchangeCode(string $code, string $overrideRedirectUri = ''): array {
    $clientId     = trim((string)env('GOOGLE_CLIENT_ID', ''));
    $clientSecret = trim((string)env('GOOGLE_CLIENT_SECRET', ''));
    $redirectUri  = $overrideRedirectUri !== '' ? rtrim($overrideRedirectUri, '/') : getGoogleRedirectUri();

    return googleHttpPost('https://oauth2.googleapis.com/token', [
        'code'          => $code,
        'client_id'     => $clientId,
        'client_secret' => $clientSecret,
        'redirect_uri'  => $redirectUri,
        'grant_type'    => 'authorization_code',
    ]);
}

/**
 * Decodifica y valida los claims del JWT id_token obtenido directamente del endpoint
 * autoritativo https://oauth2.googleapis.com/token (sobre TLS con client_secret).
 * Evita un segundo roundtrip innecesario y propenso a timeouts hacia tokeninfo.
 */
function googleDecodeIdToken(string $idToken): array {
    $payload = null;
    $parts = explode('.', $idToken);
    if (count($parts) === 3) {
        $b64 = strtr($parts[1], '-_', '+/');
        $pad = strlen($b64) % 4;
        if ($pad > 0) $b64 .= str_repeat('=', 4 - $pad);
        $decoded = json_decode((string)base64_decode($b64, true), true);
        if (is_array($decoded) && !empty($decoded['sub']) && !empty($decoded['email'])) {
            $payload = $decoded;
        }
    }

    // Fallback a tokeninfo solo si el JWT no pudo decodificarse localmente
    if ($payload === null) {
        $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
        $payload = googleHttpGet($url);
    }

    // Verificar emisor (iss)
    $iss = (string)($payload['iss'] ?? '');
    if (!in_array($iss, ['https://accounts.google.com', 'accounts.google.com'], true)) {
        throw new \RuntimeException('Emisor de token de Google no válido.');
    }

    // Verificar expiración (exp) con 60s de tolerancia de reloj
    if (empty($payload['exp']) || (int)$payload['exp'] < (time() - 60)) {
        throw new \RuntimeException('El token de Google ha expirado.');
    }

    // Verificar que el token pertenezca a nuestra app (aud)
    $expectedClientId = env('GOOGLE_CLIENT_ID', '');
    if (
        !empty($expectedClientId) &&
        $expectedClientId !== 'PENDIENTE_CONFIGURAR' &&
        ($payload['aud'] ?? '') !== $expectedClientId
    ) {
        throw new \RuntimeException('Token de Google no pertenece a esta aplicación.');
    }

    return $payload;
}

