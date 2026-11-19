<?php
// controllers/AuthController.php
declare(strict_types=1);

require_once BASE_DIR . '/models/UsuarioModel.php';

$action = $segment[1] ?? 'login';

// Asegurar helpers CSRF disponibles en vistas
if (!function_exists('csrfToken')) {
    function csrfToken(): string { return csrfGenerate(); }
}
if (!function_exists('csrfField')) {
    function csrfField(): string {
        return '<input type="hidden" name="csrf_token" value="' . csrfGenerate() . '">';
    }
}

match($action) {
    'login'                       => authLogin(),
    'standby'                     => authLogin('standby'),
    'do-login'                    => authDoLogin(),
    'registro'                    => authRegistro(),
    'do-registro'                 => authDoRegistro(),
    'api-validar-codigo'          => authApiValidarCodigo(),
    'api-enviar-codigo-email'     => authApiEnviarCodigoEmail(),
    'api-verificar-codigo-email'  => authApiVerificarCodigoEmail(),
    'logout'                      => authLogout(),
    'google'                      => authGoogleRedirect(),
    'google-callback'             => authGoogleCallback(),
    'recuperar'                   => authRecuperar(),
    'do-recuperar-identificar'    => authDoRecuperarIdentificar(),
    'do-recuperar-verificar-resp' => authDoRecuperarVerificarRespuesta(),
    'do-recuperar-cambiar'        => authDoRecuperarCambiar(),
    default                       => authLogin(),
};

// ══════════════════════════════════════════════════════════════
//  VISTAS
// ══════════════════════════════════════════════════════════════

function authLogin(string $vistaInicial = 'login'): void {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    $tieneRefUrl = false;
    if (!empty($_GET['ref']) && is_string($_GET['ref'])) {
        $tieneRefUrl = true;
        $refRaw = strtoupper(trim($_GET['ref']));
        $refCheck = resolverEntrenadorPorCodigo($refRaw);
        $codigoGuardar = $refCheck ? (string)$refCheck['codigo_referido'] : $refRaw;
        if ($codigoGuardar !== '') {
            $_SESSION['ref_codigo'] = $codigoGuardar;
            $isHttpsCookie = (
                (!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ||
                (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
            );
            @setcookie('xp_ref_code', $codigoGuardar, [
                'expires'  => time() + (14 * 86400),
                'path'     => '/',
                'secure'   => $isHttpsCookie,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $vistaInicial = 'register';
    }

    // Si NO viene con link de referido (?ref=...), redirigir al dashboard si ya tiene sesión activa
    if (!$tieneRefUrl && !empty($_SESSION['user_id'])) {
        $rol = $_SESSION['rol'] ?? '';
        if ($rol === 'admin') {
            redirect('admin/rutina');
        } elseif ($rol === 'entrenador') {
            redirect('entrenador/dashboard');
        } elseif ($rol === 'cliente' || $rol === 'atleta') {
            $_SESSION['rol'] = 'cliente'; // Failsafe normalization
            redirect('cliente/dashboard');
        } else {
            // Role is invalid, destroy session to prevent redirect loops
            $_SESSION = [];
            session_destroy();
        }
    }
    $flash = getFlash();
    // login.php contiene las 3 vistas (#view-login / #view-register / #view-recovery) sobre el mismo fondo.
    $vistaInicial = in_array($vistaInicial, ['register', 'recovery', 'standby'], true) ? $vistaInicial : 'login';
    require BASE_DIR . '/views/auth/login.php';
}

function authApiValidarCodigo(): void {
    $codigo = trim((string)($_GET['codigo'] ?? $_POST['codigo'] ?? ''));
    if ($codigo === '') {
        jsonOut(false, 'Código vacío');
    }
    $info = resolverEntrenadorPorCodigo($codigo);
    if (!$info) {
        jsonOut(false, 'Código no encontrado');
    }
    $_SESSION['ref_codigo'] = $info['codigo_referido'];
    @setcookie('xp_ref_code', $info['codigo_referido'], [
        'expires'  => time() + (14 * 86400),
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    jsonOut(true, 'Entrenador verificado', [
        'entrenador_id'     => (int)$info['entrenador_id'],
        'codigo_referido'   => (string)$info['codigo_referido'],
        'entrenador_nombre' => (string)$info['entrenador_nombre'],
    ]);
}

/** Valida formato, bloquea dominios desechables/ficticios y verifica registro DNS MX/A del dominio. */
function _validarDominioCorreoReal(string $email): ?string {
    $email = strtolower(trim($email));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'El correo electrónico no es válido.';
    }

    $emailParts = explode('@', $email);
    $emailDomain = strtolower(trim($emailParts[1] ?? ''));
    if ($emailDomain === '' || str_ends_with($emailDomain, '.test') || str_ends_with($emailDomain, '.invalid') || str_ends_with($emailDomain, '.local')) {
        return 'Ingresa un correo electrónico real y operativo.';
    }

    $disposableDomains = [
        '10minutemail.com', 'tempmail.com', 'temp-mail.org', 'temp-mail.io', 'guerrillamail.com',
        'guerrillamail.info', 'guerrillamail.net', 'yopmail.com', 'yopmail.fr', 'mailinator.com',
        'trashmail.com', 'trash-mail.com', 'throwawaymail.com', 'dispostable.com', 'fakeinbox.com',
        'mytemp.email', 'getairmail.com', 'sharklasers.com', 'nada.ltd', 'mohmal.com',
        'crazymailing.com', 'maildrop.cc', 'mintemail.com', 'emailondeck.com', 'burnermail.io',
        'anonbox.net', 'inboxkitten.com', 'harakirimail.com', 'spamgourmet.com', 'tempr.email',
        'example.com', 'example.org', 'example.net', 'test.com', 'localhost'
    ];
    if (in_array($emailDomain, $disposableDomains, true)) {
        return 'No se permiten correos electrónicos temporales, ficticios o desechables.';
    }

    $trustedDomains = ['gmail.com', 'hotmail.com', 'yahoo.com', 'outlook.com', 'icloud.com', 'live.com', 'proton.me', 'protonmail.com'];
    if (!in_array($emailDomain, $trustedDomains, true) && function_exists('checkdnsrr')) {
        try {
            $hasMx = checkdnsrr($emailDomain, 'MX') || checkdnsrr($emailDomain, 'A');
            if (!$hasMx) {
                return "El dominio del correo (@{$emailDomain}) no existe o no puede recibir mensajes. Ingresa un correo real.";
            }
        } catch (\Throwable $dnsEx) {}
    }

    return null;
}

/** Envía un código OTP de 6 dígitos al correo para verificar propiedad real antes de registrarse. */
function authApiEnviarCodigoEmail(): void {
    csrfCheck();
    rateLimitCheck('auth/email-otp');

    $rawJson = json_decode(file_get_contents('php://input') ?: '', true);
    $emailRaw = $_POST['email'] ?? ($rawJson['email'] ?? '');
    $email = strtolower(trim((string)$emailRaw));

    $errDom = _validarDominioCorreoReal($email);
    if ($errDom !== null) {
        jsonOut(false, $errDom);
    }

    try {
        $um = new UsuarioModel();
        if ($um->emailExists($email)) {
            jsonOut(false, 'Este correo ya está registrado. Inicia sesión o utiliza otro correo.');
        }
    } catch (\Throwable $e) {
        error_log('[authApiEnviarCodigoEmail] DB check error: ' . $e->getMessage());
        jsonOut(false, 'Error al verificar disponibilidad del correo.');
    }

    $prev = $_SESSION['reg_email_otp'] ?? null;
    $now = time();
    if (is_array($prev) && ($prev['email'] ?? '') === $email && ($now - (int)($prev['sent_at'] ?? 0)) < 45) {
        $wait = 45 - ($now - (int)($prev['sent_at'] ?? 0));
        jsonOut(false, "Espera {$wait}s antes de solicitar otro código.", ['cooldown' => $wait]);
    }

    $otp = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['reg_email_otp'] = [
        'email'      => $email,
        'hash'       => password_hash($otp, PASSWORD_DEFAULT),
        'expires_at' => $now + 600,
        'sent_at'    => $now,
        'attempts'   => 0,
    ];
    unset($_SESSION['reg_email_verified']);

    $body = '
      <div style="font-family:Inter,Arial,sans-serif;background:#0b0b12;color:#f4f4f5;padding:28px;border-radius:16px;max-width:480px;margin:0 auto;border:1px solid #2b2142;">
        <h2 style="margin:0 0 10px;color:#c084fc;font-size:20px;">Verifica tu correo en Atlas Fit</h2>
        <p style="margin:0 0 16px;color:#cbd5e1;font-size:14px;line-height:1.5;">
          Ingresa este código de 6 dígitos en la pantalla de registro para confirmar que este correo es real y te pertenece:
        </p>
        <div style="background:#171226;border:1px solid #7c3aed;border-radius:12px;padding:16px;text-align:center;font-size:30px;font-weight:800;letter-spacing:8px;color:#ffffff;margin-bottom:16px;">
          ' . e($otp) . '
        </div>
        <p style="margin:0;color:#94a3b8;font-size:12px;">
          El código expira en 10 minutos. Si no solicitaste este registro, ignora este mensaje.
        </p>
      </div>';

    if (!sendEmail($email, 'Código de verificación — Atlas Fit', $body)) {
        unset($_SESSION['reg_email_otp']);
        jsonOut(false, 'No pudimos enviar el código a ese correo. Verifica que la dirección exista e inténtalo de nuevo.');
    }

    jsonOut(true, 'Código enviado a ' . $email . '. Revisa tu bandeja de entrada o spam.', ['cooldown' => 45]);
}

/** Verifica el código OTP ingresado en el registro local y sella la sesión como correo verificado. */
function authApiVerificarCodigoEmail(): void {
    csrfCheck();
    rateLimitCheck('auth/email-otp-verify');

    $rawJson = json_decode(file_get_contents('php://input') ?: '', true);
    $email  = strtolower(trim((string)($_POST['email'] ?? ($rawJson['email'] ?? ''))));
    $codigo = preg_replace('/\D+/', '', (string)($_POST['codigo'] ?? ($rawJson['codigo'] ?? ''))) ?? '';

    if ($email === '' || strlen($codigo) !== 6) {
        jsonOut(false, 'Ingresa un correo válido y el código de 6 dígitos.');
    }

    $otpData = $_SESSION['reg_email_otp'] ?? null;
    if (!is_array($otpData) || ($otpData['email'] ?? '') !== $email) {
        jsonOut(false, 'Primero solicita un código para este correo electrónico.');
    }

    if (time() > (int)($otpData['expires_at'] ?? 0)) {
        unset($_SESSION['reg_email_otp']);
        jsonOut(false, 'El código ha expirado. Solicita uno nuevo.');
    }

    $attempts = (int)($otpData['attempts'] ?? 0);
    if ($attempts >= 5) {
        unset($_SESSION['reg_email_otp']);
        jsonOut(false, 'Demasiados intentos fallidos. Solicita un nuevo código.');
    }

    if (!password_verify($codigo, (string)($otpData['hash'] ?? ''))) {
        $_SESSION['reg_email_otp']['attempts'] = $attempts + 1;
        rateLimitFail('auth/email-otp-verify');
        $restantes = 4 - $attempts;
        jsonOut(false, $restantes > 0
            ? "Código incorrecto. Te quedan {$restantes} intentos."
            : 'Demasiados intentos fallidos. Solicita un nuevo código.');
    }

    $_SESSION['reg_email_verified'] = [
        'email'       => $email,
        'verified_at' => time(),
    ];
    unset($_SESSION['reg_email_otp']);
    rateLimitReset('auth/email-otp-verify');

    jsonOut(true, 'Correo verificado correctamente.', ['email' => $email]);
}

/** /auth/registro: mismo documento que el login, abierto directamente en la vista de registro. */
function authRegistro(): void {
    authLogin('register');
}

/** /auth/recuperar: mismo documento que el login, abierto directamente en la vista de recuperación. */
function authRecuperar(): void {
    authLogin('recovery');
}

// ══════════════════════════════════════════════════════════════
//  DO-LOGIN — Con Rate Limiting y protección fuerza bruta
// ══════════════════════════════════════════════════════════════

function authDoLogin(): void {
    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
           || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
           || (!empty($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json'));

    $fail = function(string $msg, array $extra = []) use ($isAjax): never {
        if (!$isAjax) {
            setFlash('error', $msg);
            redirect('auth/login');
        }
        jsonOut(false, $msg, array_merge(['success' => false, 'mensaje' => $msg], $extra));
    };

    // ── 1. Rate Limiting (antes de cualquier procesamiento) ──
    rateLimitCheck('auth/do-login');

    // ── 2. CSRF ──
    csrfCheck();

    // ── 3. Sanitizar entradas ──
    $usuario = sanitizeInput($_POST['usuario'] ?? $_POST['email'] ?? '', 255);
    $pass    = (string)($_POST['password'] ?? '');

    if (empty($usuario) || empty($pass)) {
        $fail('Por favor completa todos los campos.');
    }

    // ── 4. Validar longitud mínima/máxima para evitar ataques con inputs enormes ──
    if (strlen($pass) < 4 || strlen($pass) > 1000) {
        rateLimitFail('auth/do-login');
        $fail('Credenciales incorrectas.');
    }

    try {
        $um   = new UsuarioModel();
        $user = $um->findByUsernameOrEmail($usuario);

        // ── 5. Verificar credenciales con timing seguro ──
        $dummyHash = '$2y$12$invalidhashtopreventtimingattacksxxxxxxxxxxxxxxxxxxx';
        $hashToVerify = $user['password_hash'] ?? $dummyHash;

        $pwdVerify = password_verify($pass, $hashToVerify);

        if (!$user || !$pwdVerify) {
            rateLimitFail('auth/do-login');
            $fail('Credenciales incorrectas.');
        }

        // ── 6. Credenciales correctas: limpiar rate limit ──
        rateLimitReset('auth/do-login');

        // ── 6.1 ONBOARDING GATE: validación inquebrantable de Stand-by ──
        $bloqueo = UsuarioModel::mensajeBloqueo($user);
        if ($bloqueo !== null) {
            $estadoCuenta = (string)($user['estado_cuenta'] ?? 'pendiente');
            $fail($bloqueo, [
                'status'         => $estadoCuenta === 'pendiente' ? 'standby' : 'rejected',
                'estado_cuenta'  => $estadoCuenta,
                'rol_solicitado' => (string)($user['rol'] ?? 'cliente'),
            ]);
        }

        // ── 7. Login exitoso: establecer sesión ──
        session_regenerate_id(true);

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['nombre']  = $user['nombre'];
        $_SESSION['email']   = $user['email'];
        $_SESSION['rol']     = $user['rol'];
        $_SESSION['_login_at'] = time();

        if (password_needs_rehash($user['password_hash'], PASSWORD_BCRYPT, ['cost' => 10])) {
            $newHash = password_hash($pass, PASSWORD_BCRYPT, ['cost' => 10]);
            $um->updatePasswordHash((int)$user['id'], $newHash);
            $user['password_hash'] = $newHash;
        }

        // Persistir sesión por 90 días en el teléfono (sobrevive al cerrar la app de recientes)
        xpSetRememberCookie($user);

        $dest = $user['rol'] === 'admin'
            ? 'admin/dashboard'
            : ($user['rol'] === 'entrenador' ? 'entrenador/dashboard' : 'cliente/dashboard');

        if (!$isAjax) {
            redirect($dest);
        }

        jsonOut(true, 'Login exitoso', [
            'success'  => true,
            'redirect' => APP_URL . '/' . $dest,
        ]);

    } catch (\Throwable $e) {
        error_log('[Xprod Fit][Auth] Error en do-login: ' . $e->getMessage());
        $fail('Error interno. Intente luego.');
    }
}

// ══════════════════════════════════════════════════════════════
//  DO-REGISTRO — Con Rate Limiting y validación estricta
// ══════════════════════════════════════════════════════════════

/** Respuesta JSON de error de validación con el campo afectado (el front lo resalta). */
function authRegistroError(string $msg, string $campo, int $status = 422): never {
    http_response_code($status);
    jsonOut(false, $msg, ['campo' => $campo]);
}

function authDoRegistro(): void {
    // ── 1. Rate Limiting (más restrictivo en registro) ──
    rateLimitCheck('auth/do-registro');

    // ── 2. CSRF ──
    csrfCheck();

    try {
        // ── 3. Sanitizar (nunca confiar en el cliente) ──
        $in        = static fn(string $k): string => isset($_POST[$k]) && is_scalar($_POST[$k]) ? (string)$_POST[$k] : '';
        $reNombre  = '/^\p{L}[\p{L}\s\'\x{2019}.\-]*$/u';
        $nombres   = preg_replace('/\s+/u', ' ', sanitizeInput($in('nombres'),   50)) ?? '';
        $apellidos = preg_replace('/\s+/u', ' ', sanitizeInput($in('apellidos'), 50)) ?? '';
        $cedula    = UsuarioModel::normalizarCedula($in('cedula'));
        $email        = sanitizeEmail($in('email'));
        $authProvider = sanitizeInput($in('auth_provider') ?: 'local', 20);
        $hasGoogleSess = !empty($_SESSION['google_onboarding']) && !empty($_SESSION['google_onboarding']['email']);
        if ($authProvider === 'google' && !$hasGoogleSess) {
            authRegistroError('Tu sesión verificada con Google expiró o no es válida. Vuelve a continuar con Google.', 'email', 403);
        }
        $isGoogle     = $hasGoogleSess;
        if ($isGoogle) {
            // En onboarding con Google, el correo verificado por OAuth en sesión es la única fuente de verdad
            $email = sanitizeEmail((string)$_SESSION['google_onboarding']['email']);
        }
        $pass         = $in('password');
        $pregunta     = sanitizeInput($in('pregunta_seguridad'),  100);
        $respuesta    = sanitizeInput($in('respuesta_seguridad'), 100);

        // Campos del Onboarding Wizard (Composición y Metas)
        $fechaNacRaw = trim($in('fecha_nacimiento') ?: $in('fecha_nac'));
        $fechaNac    = preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaNacRaw) ? $fechaNacRaw : '2005-06-15';
        
        $pesoRaw     = (float)($in('peso_kg') ?: $in('peso'));
        if ($pesoRaw < 35 || $pesoRaw > 160) {
            authRegistroError('El peso corporal debe estar entre 35 kg y 160 kg.', 'peso_kg');
        }
        $pesoKg      = round($pesoRaw, 1);

        $alturaRaw   = (float)($in('altura_cm') ?: $in('altura'));
        $alturaCm    = ($alturaRaw >= 100 && $alturaRaw <= 250) ? round($alturaRaw) : 170.0;

        // Metas seleccionadas (array o JSON estructurado)
        $metas = [];
        $metaDetalle = null;
        $metaOmitida = false;

        if (!empty($_POST['metas_json']) && is_string($_POST['metas_json'])) {
            $decoded = json_decode($_POST['metas_json'], true);
            if (is_array($decoded)) {
                if (isset($decoded['omitido']) && $decoded['omitido'] === true) {
                    $metaOmitida = true;
                    $metaDetalle = [
                        'id'             => null,
                        'titulo'         => null,
                        'subnivel'       => null,
                        'custom_texto'   => null,
                        'omitido'        => true,
                        'actualizado_en' => date('Y-m-d H:i:s')
                    ];
                } elseif (isset($decoded['id']) || isset($decoded['titulo'])) {
                    $mTitulo = sanitizeInput($decoded['titulo'] ?? ($decoded['custom_texto'] ?? 'Meta General'), 100);
                    $subnivel = null;
                    if (!empty($decoded['subnivel']) && is_array($decoded['subnivel'])) {
                        $subnivel = [
                            'clave'     => sanitizeInput($decoded['subnivel']['clave'] ?? '', 50),
                            'titulo'    => sanitizeInput($decoded['subnivel']['titulo'] ?? '', 100),
                            'subtitulo' => sanitizeInput($decoded['subnivel']['subtitulo'] ?? '', 150),
                            'silueta'   => sanitizeInput($decoded['subnivel']['silueta'] ?? '', 50),
                            'icono'     => sanitizeInput($decoded['subnivel']['icono'] ?? 'ti-sparkles', 50)
                        ];
                    }
                    $customTexto = !empty($decoded['custom_texto']) ? sanitizeInput((string)$decoded['custom_texto'], 255) : null;
                    $metaDetalle = [
                        'id'             => sanitizeInput($decoded['id'] ?? 'meta', 50),
                        'titulo'         => $mTitulo,
                        'categoria'      => sanitizeInput($decoded['categoria'] ?? 'general', 50),
                        'icono'          => sanitizeInput($decoded['icono'] ?? 'ti-target', 50),
                        'color'          => sanitizeInput($decoded['color'] ?? '#8a2be2', 30),
                        'subnivel'       => $subnivel,
                        'custom_texto'   => $customTexto,
                        'omitido'        => false,
                        'actualizado_en' => date('Y-m-d H:i:s')
                    ];
                    $metas[] = $customTexto ?: $mTitulo;
                } else {
                    $metas = array_map(fn($m) => sanitizeInput((string)$m, 100), $decoded);
                    if (!empty($metas)) {
                        $metaDetalle = [
                            'id'             => 'general',
                            'titulo'         => $metas[0],
                            'categoria'      => 'general',
                            'icono'          => 'ti-target',
                            'color'          => '#8a2be2',
                            'subnivel'       => null,
                            'custom_texto'   => null,
                            'omitido'        => false,
                            'actualizado_en' => date('Y-m-d H:i:s')
                        ];
                    }
                }
            }
        } elseif (isset($_POST['metas']) && is_array($_POST['metas'])) {
            $metas = array_map(fn($m) => sanitizeInput((string)$m, 100), $_POST['metas']);
            if (!empty($metas)) {
                $metaDetalle = [
                    'id'             => 'general',
                    'titulo'         => $metas[0],
                    'categoria'      => 'general',
                    'icono'          => 'ti-target',
                    'color'          => '#8a2be2',
                    'subnivel'       => null,
                    'custom_texto'   => null,
                    'omitido'        => false,
                    'actualizado_en' => date('Y-m-d H:i:s')
                ];
            }
        }
        $metas = array_values(array_filter($metas));

        // Detectar si el registro es una solicitud de cuenta de Entrenador (sin código de invitación)
        $esEntrenador = in_array(strtolower(trim((string)$in('soy_entrenador'))), ['1', 'true', 'on', 'si', 'yes'], true);

        // ── 4. Validar tipos, longitudes y listas blancas ──
        if (mb_strlen($nombres) < 2 || !preg_match($reNombre, $nombres)) {
            authRegistroError('Ingresa tus nombres (mínimo 2 letras sin números ni símbolos).', 'nombres');
        }
        if (mb_strlen($apellidos) < 2 || !preg_match($reNombre, $apellidos)) {
            authRegistroError('Ingresa tus apellidos (mínimo 2 letras sin números ni símbolos).', 'apellidos');
        }
        if (!$esEntrenador && $cedula === null) {
            authRegistroError('La cédula debe contener solo números (entre 5 y 8 dígitos).', 'cedula');
        }
        if ($email === '') {
            authRegistroError('El correo electrónico no es válido.', 'email');
        }

        // ── Validación de Dominio Real, Anti-Desechables y Propiedad de Buzón ──
        $errDom = _validarDominioCorreoReal($email);
        if ($errDom !== null) {
            authRegistroError($errDom, 'email');
        }

        if (!$isGoogle) {
            $verifEmail = $_SESSION['reg_email_verified'] ?? null;
            $emailLower = strtolower(trim($email));
            $correoVerificadoOk = is_array($verifEmail)
                && ($verifEmail['email'] ?? '') === $emailLower
                && (time() - (int)($verifEmail['verified_at'] ?? 0)) <= 1800;
            if (!$correoVerificadoOk) {
                authRegistroError('Debes verificar tu correo electrónico real con el código de 6 dígitos enviado a tu bandeja.', 'email', 422);
            }
        }

        $um = new UsuarioModel();
        $db = getDB();
        $clienteId = null;

        // ── 4.B FLUJO EXCLUSIVO DE SOLICITUD DE ENTRENADOR (sin código de entrenador) ──
        if ($esEntrenador) {
            $nombreCompleto = trim($nombres . ' ' . $apellidos);

            if (!$isGoogle) {
                if (strlen($pass) < 8) {
                    authRegistroError('La contraseña debe tener al menos 8 caracteres.', 'password');
                }
                if (strlen($pass) > 1000) {
                    authRegistroError('Contraseña demasiado larga.', 'password');
                }
                if (!in_array($pregunta, UsuarioModel::PREGUNTAS_SEGURIDAD, true)) {
                    authRegistroError('Selecciona una pregunta de seguridad.', 'pregunta_seguridad');
                }
                if (mb_strlen($respuesta) < 2) {
                    authRegistroError('La respuesta de seguridad es obligatoria (mínimo 2 caracteres).', 'respuesta_seguridad');
                }
                if ($um->emailExists($email)) {
                    authRegistroError('Este correo ya está registrado.', 'email', 409);
                }

                $uid = $um->registrarEntrenadorPendiente(
                    $nombreCompleto,
                    $email,
                    password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]),
                    $pregunta,
                    password_hash(strtolower(trim($respuesta)), PASSWORD_DEFAULT)
                );
            } else {
                $googleSession = $_SESSION['google_onboarding'] ?? [];
                $targetUid     = !empty($googleSession['user_id']) ? (int)$googleSession['user_id'] : null;
                $user          = $targetUid ? $um->findById($targetUid) : $um->findByEmail($email);

                if ($user && ($user['rol'] ?? '') === 'admin') {
                    authRegistroError('No se puede cambiar el rol de una cuenta administradora.', 'email', 403);
                }

                $db->beginTransaction();
                try {
                    if ($user) {
                        $uid = (int)$user['id'];
                        $db->prepare("UPDATE usuarios SET nombre = ?, rol = 'entrenador', activo = 0, estado_cuenta = 'pendiente', auth_provider = 'google' WHERE id = ?")
                           ->execute([$nombreCompleto, $uid]);
                        // Limpiar fila vacía de clientes si fue creada por el onboarding temporal de Google
                        $db->prepare("DELETE FROM clientes WHERE usuario_id = ? AND cedula IS NULL AND peso_kg IS NULL AND altura_cm IS NULL")
                           ->execute([$uid]);
                    } else {
                        $googleId  = $googleSession['google_id'] ?? null;
                        $avatarUrl = $googleSession['avatar_url'] ?? null;
                        $stIns = $db->prepare(
                            "INSERT INTO usuarios (nombre, email, password_hash, rol, google_id, auth_provider, avatar_url, activo, estado_cuenta)
                             VALUES (?, ?, '', 'entrenador', ?, 'google', ?, 0, 'pendiente')"
                        );
                        $stIns->execute([$nombreCompleto, $email, $googleId, $avatarUrl]);
                        $uid = (int)$db->lastInsertId();
                    }
                    $db->commit();
                } catch (\Throwable $eTx) {
                    if ($db->inTransaction()) {
                        $db->rollBack();
                    }
                    throw $eTx;
                }
            }

            // Notificar a todos los Super Admins activos en la campana del Header y panel de Gestión
            try {
                $admins = $db->query("SELECT id FROM usuarios WHERE rol = 'admin' AND activo = 1")->fetchAll(\PDO::FETCH_COLUMN);
                $msgAdmin    = 'Nuevo entrenador en espera: ' . $nombreCompleto . ' solicita activación';
                $enlaceAdmin = APP_URL . '/admin/gestion';
                foreach ($admins as $admIdRaw) {
                    $admId = (int)$admIdRaw;
                    if ($admId <= 0) continue;
                    registrarNotificacionSistema([
                        'remitente_id'     => $uid,
                        'destinatario_id'  => $admId,
                        'rol_destinatario' => 'admin',
                        'tipo_evento'      => 'solicitud_entrenador',
                        'referencia_id'    => $uid,
                        'entrenador_id'    => $uid,
                        'mensaje'          => $msgAdmin,
                        'enlace'           => $enlaceAdmin,
                        'metadatos'        => ['entrenador_usuario_id' => $uid, 'email' => $email],
                    ]);
                }
            } catch (\Throwable $eNotif) {
                error_log('[authDoRegistro][entrenador] Notificación admin no creada: ' . $eNotif->getMessage());
            }

            unset($_SESSION['google_onboarding'], $_SESSION['reg_email_otp'], $_SESSION['reg_email_verified']);
            unset($_SESSION['user_id'], $_SESSION['nombre'], $_SESSION['email'], $_SESSION['rol'], $_SESSION['_login_at'], $_SESSION['_via']);

            jsonOut(true, UsuarioModel::MSG_PENDIENTE_ENTRENADOR, [
                'status'         => 'standby',
                'estado_cuenta'  => 'pendiente',
                'rol_solicitado' => 'entrenador',
                'mensaje'        => 'En espera de que el administrador active tu cuenta de entrenador',
            ]);
        }

        $codigoInput = strtoupper(trim(sanitizeInput($in('codigo_entrenador'), 40)));
        $codigoFallback = strtoupper(trim((string)($_SESSION['ref_codigo'] ?? ($_COOKIE['xp_ref_code'] ?? ''))));
        $codigoEntrenador = $codigoInput !== '' ? $codigoInput : $codigoFallback;

        if ($codigoEntrenador === '') {
            authRegistroError('Debes ingresar el código o usar el link de invitación de tu entrenador para registrarte.', 'codigo_entrenador');
        }

        $refEnt = resolverEntrenadorPorCodigo($codigoEntrenador);
        if (!$refEnt) {
            authRegistroError('El código de entrenador ingresado no existe o no está activo.', 'codigo_entrenador');
        }

        $entrenadorId = (int)$refEnt['entrenador_id'];

        if (!$isGoogle) {
            if (strlen($pass) < 8) {
                authRegistroError('La contraseña debe tener al menos 8 caracteres.', 'password');
            }
            if (strlen($pass) > 1000) {
                authRegistroError('Contraseña demasiado larga.', 'password');
            }
            if (!in_array($pregunta, UsuarioModel::PREGUNTAS_SEGURIDAD, true)) {
                authRegistroError('Selecciona una pregunta de seguridad.', 'pregunta_seguridad');
            }
            if (mb_strlen($respuesta) < 2) {
                authRegistroError('La respuesta de seguridad es obligatoria (mínimo 2 caracteres).', 'respuesta_seguridad');
            }

            // ── 5. Unicidad ──
            if ($um->emailExists($email)) {
                authRegistroError('Este correo ya está registrado.', 'email', 409);
            }
            if ($um->cedulaExists($cedula)) {
                authRegistroError('Esta cédula ya está registrada.', 'cedula', 409);
            }

            // ── 6. Alta atómica: usuarios (estado_cuenta='pendiente') + clientes + historial + metas ──
            $ids = $um->registrarAtleta(
                $nombres . ' ' . $apellidos,
                $email,
                password_hash($pass, PASSWORD_BCRYPT, ['cost' => 12]),
                $cedula,
                $pregunta,
                password_hash(strtolower(trim($respuesta)), PASSWORD_DEFAULT),
                $entrenadorId > 0 ? $entrenadorId : null,
                $fechaNac,
                $pesoKg,
                $alturaCm,
                $metas,
                $metaDetalle
            );
            $clienteId = $ids['cliente_id'];
            $uid       = (int)($ids['usuario_id'] ?? 0);
        } else {
            // ── Flujo Google: Guardar perfil completo en usuarios y clientes con estado_cuenta='pendiente' ──
            $googleSession = $_SESSION['google_onboarding'] ?? [];
            $targetUid     = !empty($googleSession['user_id']) ? (int)$googleSession['user_id'] : null;
            $user          = $targetUid ? $um->findById($targetUid) : $um->findByEmail($email);

            // Verificar si la cédula ya pertenece a otro cliente
            $stCed = $db->prepare("SELECT usuario_id FROM clientes WHERE cedula = ? LIMIT 1");
            $stCed->execute([$cedula]);
            $ownerUid = $stCed->fetchColumn();
            if ($ownerUid && (!$user || (int)$ownerUid !== (int)$user['id'])) {
                authRegistroError('Esta cédula ya está registrada por otro atleta.', 'cedula', 409);
            }

            $db->beginTransaction();
            try {
                if ($user) {
                    $uid = (int)$user['id'];
                    $db->prepare("UPDATE usuarios SET nombre = ?, estado_cuenta = 'pendiente', auth_provider = 'google' WHERE id = ?")
                       ->execute([$nombres . ' ' . $apellidos, $uid]);
                } else {
                    $googleId  = $googleSession['google_id'] ?? null;
                    $avatarUrl = $googleSession['avatar_url'] ?? null;
                    $stIns = $db->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, google_id, auth_provider, avatar_url, estado_cuenta) VALUES (?, ?, '', 'cliente', ?, 'google', ?, 'pendiente')");
                    $stIns->execute([$nombres . ' ' . $apellidos, $email, $googleId, $avatarUrl]);
                    $uid = (int)$db->lastInsertId();
                }

                $metaDetalleJson = $metaDetalle ? json_encode($metaDetalle, JSON_UNESCAPED_UNICODE) : null;
                $objetivoTexto = !empty($metaDetalle['custom_texto'])
                    ? $metaDetalle['custom_texto']
                    : (!empty($metaDetalle['titulo']) ? $metaDetalle['titulo'] : (!empty($metas) ? implode(', ', $metas) : null));

                $stCli = $db->prepare("SELECT id FROM clientes WHERE usuario_id = ? LIMIT 1");
                $stCli->execute([$uid]);
                $cRowId = $stCli->fetchColumn();

                if ($cRowId) {
                    $clienteId = (int)$cRowId;
                    $db->prepare("UPDATE clientes SET cedula = ?, entrenador_id = ?, peso_kg = ?, altura_cm = ?, fecha_nac = ?, objetivo = ?, meta_detalle = ? WHERE id = ?")
                       ->execute([$cedula, $entrenadorId > 0 ? $entrenadorId : null, $pesoKg, $alturaCm, $fechaNac, $objetivoTexto, $metaDetalleJson, $clienteId]);
                } else {
                    $stInsCli = $db->prepare("INSERT INTO clientes (usuario_id, cedula, entrenador_id, peso_kg, altura_cm, fecha_nac, objetivo, meta_detalle) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stInsCli->execute([$uid, $cedula, $entrenadorId > 0 ? $entrenadorId : null, $pesoKg, $alturaCm, $fechaNac, $objetivoTexto, $metaDetalleJson]);
                    $clienteId = (int)$db->lastInsertId();
                }

                // Guardar peso en el historial
                if ($pesoKg !== null && $pesoKg > 0) {
                    $db->prepare("INSERT INTO peso_historial (cliente_id, peso_kg, fecha, tipo) VALUES (?, ?, CURDATE(), 'manual')")
                       ->execute([$clienteId, $pesoKg]);
                }

                // Sincronizar metas y objetivos
                if (!empty($metas)) {
                    $stCat = $db->prepare("SELECT id, nombre, icono, color FROM objetivos_catalogo WHERE LOWER(TRIM(nombre)) = LOWER(TRIM(?)) LIMIT 1");
                    $stInsCO = $db->prepare("INSERT IGNORE INTO cliente_objetivos (cliente_id, objetivo_id, prioridad) VALUES (?, ?, ?)");
                    $stInsMeta = $db->prepare("INSERT INTO metas (cliente_id, titulo, icono, color, target_peso, fecha_inicio, semanas_duracion, estado) VALUES (?, ?, ?, ?, ?, CURDATE(), 8, 'activa')");

                    $prioridad = 1;
                    foreach ($metas as $mTitulo) {
                        $mTitulo = trim((string)$mTitulo);
                        if ($mTitulo === '') continue;

                        $stCat->execute([$mTitulo]);
                        $cat = $stCat->fetch(\PDO::FETCH_ASSOC);
                        $objId = $cat['id'] ?? null;
                        $icono = $cat['icono'] ?? 'ti-target';
                        $color = $cat['color'] ?? '#8a2be2';

                        if (!$objId) {
                            $insCat = $db->prepare("INSERT INTO objetivos_catalogo (nombre, icono, color) VALUES (?, ?, ?)");
                            $insCat->execute([$mTitulo, $icono, $color]);
                            $objId = (int)$db->lastInsertId();
                        }
                        if ($objId) {
                            $stInsCO->execute([$clienteId, $objId, $prioridad]);
                        }
                        try {
                            $stInsMeta->execute([$clienteId, $mTitulo, $icono, $color, $pesoKg]);
                        } catch (\Throwable $eMeta) {}
                        $prioridad++;
                    }
                }

                $db->commit();
            } catch (\Throwable $eTx) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $eTx;
            }
        }

        // ── 7. Notificación al Entrenador en la campana del Header ──
        if ($entrenadorId > 0 && $clienteId) {
            try {
                $msg    = 'Nuevo atleta en espera: ' . $nombres . ' ' . $apellidos . ' solicita activación';
                $enlace = APP_URL . '/entrenador/perfil-cliente?id=' . $clienteId;
                registrarNotificacionSistema([
                    'remitente_id'     => ($uid ?? 0) ?: null,
                    'destinatario_id'  => $entrenadorId,
                    'rol_destinatario' => 'entrenador',
                    'tipo_evento'      => 'registro',
                    'referencia_id'    => $clienteId,
                    'cliente_id'       => $clienteId,
                    'entrenador_id'    => $entrenadorId,
                    'mensaje'          => $msg,
                    'enlace'           => $enlace,
                    'metadatos'        => ['cliente_id' => $clienteId]
                ]);
            } catch (\Throwable $e) {
                error_log('[authDoRegistro] Notificación no creada: ' . $e->getMessage());
            }
        }

        // SEGURIDAD ESTRICTA: PROHIBIDO iniciar variables de sesión ($_SESSION) o redirigir al dashboard tras registrarse
        unset($_SESSION['google_onboarding'], $_SESSION['reg_email_otp'], $_SESSION['reg_email_verified']);
        unset($_SESSION['user_id'], $_SESSION['nombre'], $_SESSION['email'], $_SESSION['rol'], $_SESSION['_login_at'], $_SESSION['_via']);

        jsonOut(true, UsuarioModel::MSG_PENDIENTE, [
            'status'        => 'standby',
            'estado_cuenta' => 'pendiente',
            'mensaje'       => 'En espera de que tu entrenador active tu cuenta',
        ]);

    } catch (\Throwable $e) {
        // Carrera: otro request insertó el mismo email/cédula entre la verificación y el INSERT
        if ($e instanceof \PDOException && str_contains($e->getMessage(), 'Duplicate')) {
            $esCedula = str_contains($e->getMessage(), 'cedula');
            authRegistroError(
                $esCedula ? 'Esta cédula ya está registrada.' : 'Este correo ya está registrado.',
                $esCedula ? 'cedula' : 'email',
                409
            );
        }
        error_log('[authDoRegistro] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
        http_response_code(500);
        jsonOut(false, 'Error al registrar. Intente más tarde.');
    }
}

// ══════════════════════════════════════════════════════════════
//  RECUPERACIÓN DINÁMICA POR PREGUNTA DE SEGURIDAD (ui-ux-pro-max)
// ══════════════════════════════════════════════════════════════

/**
 * Paso 1: Identificación por Correo o Cédula.
 * Devuelve la pregunta de seguridad configurada para el usuario.
 */
function authDoRecuperarIdentificar(): void {
    rateLimitCheck('auth/recuperar');
    csrfCheck();

    try {
        $identificador = trim((string)($_POST['identificador'] ?? ''));
        if ($identificador === '') {
            jsonOut(false, 'Ingresa tu correo electrónico o cédula.', ['campo' => 'identificador']);
        }

        $db = getDB();
        $cedulaNorm = UsuarioModel::normalizarCedula($identificador);

        $sql = "SELECT u.id, u.nombre, u.email, u.pregunta_seguridad, u.respuesta_seguridad, u.activo
                FROM usuarios u
                LEFT JOIN clientes c ON c.usuario_id = u.id
                WHERE (LOWER(TRIM(u.email)) = LOWER(?) " . ($cedulaNorm ? "OR c.cedula = ? OR c.cedula = ?" : "") . ")
                LIMIT 1";

        $params = [strtolower($identificador)];
        if ($cedulaNorm) {
            $params[] = $cedulaNorm;
            $params[] = strtoupper($identificador);
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || empty($user['activo'])) {
            rateLimitFail('auth/recuperar');
            jsonOut(false, 'No encontramos ninguna cuenta activa asociada a este correo o cédula.', ['campo' => 'identificador']);
        }

        if (empty($user['pregunta_seguridad']) || empty($user['respuesta_seguridad'])) {
            jsonOut(false, 'Esta cuenta no posee preguntas de seguridad configuradas. Contacta a tu entrenador.', ['campo' => 'identificador']);
        }

        $_SESSION['recovery_user_id'] = (int)$user['id'];
        $_SESSION['recovery_identificador'] = $user['email'];
        $_SESSION['recovery_expires'] = time() + 900; // 15 minutos

        jsonOut(true, 'Identidad confirmada.', [
            'pregunta'      => $user['pregunta_seguridad'],
            'identificador' => $identificador,
        ]);

    } catch (\Throwable $e) {
        error_log('[authDoRecuperarIdentificar] ' . $e->getMessage());
        http_response_code(500);
        jsonOut(false, 'Error interno del servidor. Intente más tarde.');
    }
}

/**
 * Paso 2: Validación de la Respuesta Secreta.
 * Si es correcta, habilita el paso de cambio de contraseña en la sesión.
 */
function authDoRecuperarVerificarRespuesta(): void {
    rateLimitCheck('auth/recuperar');
    csrfCheck();

    try {
        $identificador = trim((string)($_POST['identificador'] ?? ''));
        $respuesta     = trim((string)($_POST['respuesta'] ?? ''));

        if ($identificador === '') {
            jsonOut(false, 'Falta el identificador de cuenta.', ['campo' => 'identificador']);
        }
        if (mb_strlen($respuesta) < 2) {
            jsonOut(false, 'Ingresa tu respuesta de seguridad.', ['campo' => 'respuesta']);
        }

        $db = getDB();
        $cedulaNorm = UsuarioModel::normalizarCedula($identificador);

        $sql = "SELECT u.id, u.nombre, u.email, u.respuesta_seguridad, u.activo
                FROM usuarios u
                LEFT JOIN clientes c ON c.usuario_id = u.id
                WHERE (LOWER(TRIM(u.email)) = LOWER(?) " . ($cedulaNorm ? "OR c.cedula = ? OR c.cedula = ?" : "") . ")
                LIMIT 1";

        $params = [strtolower($identificador)];
        if ($cedulaNorm) {
            $params[] = $cedulaNorm;
            $params[] = strtoupper($identificador);
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || empty($user['activo'])) {
            rateLimitFail('auth/recuperar');
            jsonOut(false, 'Cuenta no encontrada o inactiva.', ['campo' => 'identificador']);
        }

        $hashRespuesta = (string)($user['respuesta_seguridad'] ?? '');
        if (empty($hashRespuesta) || !password_verify(strtolower(trim($respuesta)), $hashRespuesta)) {
            rateLimitFail('auth/recuperar');
            jsonOut(false, 'La respuesta de seguridad es incorrecta.', ['campo' => 'respuesta']);
        }

        // Marcar en sesión que el usuario validó exitosamente su pregunta secreta
        $_SESSION['recovery_user_id']       = (int)$user['id'];
        $_SESSION['recovery_identificador'] = $user['email'];
        $_SESSION['recovery_verified']      = true;
        $_SESSION['recovery_verified_time'] = time();

        jsonOut(true, '¡Respuesta de seguridad verificada correctamente!');

    } catch (\Throwable $e) {
        error_log('[authDoRecuperarVerificarRespuesta] ' . $e->getMessage());
        http_response_code(500);
        jsonOut(false, 'Error interno al verificar respuesta.');
    }
}

/**
 * Paso 3: Actualización de Contraseña con doble campo (contraseña y confirmación).
 */
function authDoRecuperarCambiar(): void {
    rateLimitCheck('auth/recuperar');
    csrfCheck();

    try {
        // Verificar que la sesión previa pasó el Paso 2 de forma segura
        $userId       = (int)($_SESSION['recovery_user_id'] ?? 0);
        $isVerified   = !empty($_SESSION['recovery_verified']) && $_SESSION['recovery_verified'] === true;
        $verifiedTime = (int)($_SESSION['recovery_verified_time'] ?? 0);

        if (!$userId || !$isVerified || (time() - $verifiedTime > 900)) {
            unset(
                $_SESSION['recovery_user_id'],
                $_SESSION['recovery_identificador'],
                $_SESSION['recovery_expires'],
                $_SESSION['recovery_verified'],
                $_SESSION['recovery_verified_time']
            );
            jsonOut(false, 'La sesión de recuperación ha expirado o no es válida. Por favor, inicia el proceso nuevamente.');
        }

        $nuevaPass     = (string)($_POST['nueva_password'] ?? '');
        $confirmarPass = (string)($_POST['confirmar_password'] ?? '');

        if (strlen($nuevaPass) < 8) {
            jsonOut(false, 'La nueva contraseña debe tener al menos 8 caracteres.', ['campo' => 'nueva_password']);
        }
        if (strlen($nuevaPass) > 1000) {
            jsonOut(false, 'Contraseña demasiado larga.', ['campo' => 'nueva_password']);
        }
        if ($nuevaPass !== $confirmarPass) {
            jsonOut(false, 'Las contraseñas no coinciden. Por favor verifica ambos campos.', ['campo' => 'confirmar_password']);
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT id, email, activo FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user || empty($user['activo'])) {
            jsonOut(false, 'Usuario no encontrado o inactivo.');
        }

        // Actualizar contraseña con bcrypt seguro (cost 12)
        $hashPass = password_hash($nuevaPass, PASSWORD_BCRYPT, ['cost' => 12]);
        $upd = $db->prepare("UPDATE usuarios SET password_hash = ?, token_recuperacion = NULL, expiracion_token = NULL WHERE id = ?");
        $upd->execute([$hashPass, $userId]);

        $userEmail = $user['email'];
        unset(
            $_SESSION['recovery_user_id'],
            $_SESSION['recovery_identificador'],
            $_SESSION['recovery_expires'],
            $_SESSION['recovery_verified'],
            $_SESSION['recovery_verified_time']
        );

        jsonOut(true, '¡Contraseña actualizada con éxito! Ya puedes iniciar sesión con tu nueva contraseña.', [
            'email' => $userEmail
        ]);

    } catch (\Throwable $e) {
        error_log('[authDoRecuperarCambiar] ' . $e->getMessage());
        http_response_code(500);
        jsonOut(false, 'Error interno al actualizar contraseña.');
    }
}

// ══════════════════════════════════════════════════════════════
//  LOGOUT
// ══════════════════════════════════════════════════════════════

function authLogout(): void {
    // Destruir sesión y cookie persistente de 90 días de forma segura
    xpClearRememberCookie();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
    redirect('auth/login?logged_out=1');
}

// ══════════════════════════════════════════════════════════════
//  GOOGLE OAUTH 2.0
// ══════════════════════════════════════════════════════════════

function authGoogleRedirect(): void {
    if (!empty($_GET['ref']) && is_string($_GET['ref'])) {
        $refInfo = resolverEntrenadorPorCodigo($_GET['ref']);
        $codeToSave = $refInfo ? (string)$refInfo['codigo_referido'] : strtoupper(trim($_GET['ref']));
        if ($codeToSave !== '') {
            $_SESSION['ref_codigo'] = $codeToSave;
            @setcookie('xp_ref_code', $codeToSave, [
                'expires'  => time() + (14 * 86400),
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
    }
    require_once BASE_DIR . '/controllers/GoogleAuthController.php';
    googleRedirect();
}

function authGoogleCallback(): void {
    require_once BASE_DIR . '/controllers/GoogleAuthController.php';
    googleCallback();
}
