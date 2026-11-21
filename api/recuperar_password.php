<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname(__DIR__));
}
require_once dirname(__DIR__) . '/core/env.php';
loadEnv(dirname(__DIR__) . '/.env');
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/core/helpers.php';
require_once dirname(__DIR__) . '/core/rate_limiter.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
    exit;
}

rateLimitCheck('api_recovery');

try {
    $pdo = getDB();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error de conexión a la base de datos."]);
    exit;
}

$accion = $_POST['accion'] ?? '';

if ($accion === 'obtener_pregunta') {
    $email = trim((string)($_POST['email'] ?? ''));
    if (empty($email)) {
        echo json_encode(["success" => false, "message" => "El correo electrónico es requerido."]);
        exit;
    }
    try {
        $stmt = $pdo->prepare("SELECT pregunta_seguridad FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && !empty($usuario['pregunta_seguridad'])) {
            echo json_encode(["success" => true, "pregunta" => $usuario['pregunta_seguridad']]);
        } else {
            rateLimitFail('api_recovery');
            echo json_encode(["success" => false, "message" => "Usuario no encontrado, inactivo o sin pregunta de seguridad configurada."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error de base de datos."]);
    }
    exit;
}

if ($accion === 'verificar_respuesta') {
    $email = trim((string)($_POST['email'] ?? ''));
    $respuesta = (string)($_POST['respuesta'] ?? '');

    if (empty($email) || empty($respuesta)) {
        echo json_encode(["success" => false, "message" => "Correo electrónico y respuesta son requeridos."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, nombre, respuesta_seguridad FROM usuarios WHERE email = :email AND activo = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $hashSeguridad = $usuario['respuesta_seguridad'] ?? '';
            if (!empty($hashSeguridad) && password_verify(strtolower(trim($respuesta)), $hashSeguridad)) {
                // Generar código criptográficamente seguro de 6 dígitos
                $codigo = sprintf('%06d', random_int(0, 999999));
                
                // Actualizar DB con token y expiración de 15 minutos
                $upd = $pdo->prepare("UPDATE usuarios SET token_recuperacion = ?, expiracion_token = DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id = ?");
                $upd->execute([$codigo, $usuario['id']]);
                
                // Enviar Correo con PHPMailer
                $body = "Hola " . e($usuario['nombre']) . ",<br><br>Tu código de recuperación es: <strong>" . $codigo . "</strong><br>Este código expirará en 15 minutos.";
                $mailSent = sendEmail($email, "Código de Recuperación - Atlas Fit", $body);
                
                if ($mailSent) {
                    echo json_encode(["success" => true, "message" => "Código enviado al correo."]);
                } else {
                    echo json_encode(["success" => false, "message" => "Error al enviar el correo. Revisa tu configuración SMTP en el archivo .env"]);
                }
            } else {
                rateLimitFail('api_recovery');
                echo json_encode(["success" => false, "message" => "La respuesta de seguridad es incorrecta."]);
            }
        } else {
            rateLimitFail('api_recovery');
            echo json_encode(["success" => false, "message" => "Usuario no encontrado en la base de datos."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error de base de datos o correo."]);
    }
    exit;
}

if ($accion === 'verificar_codigo') {
    $email = trim((string)($_POST['email'] ?? ''));
    $codigo = trim((string)($_POST['codigo'] ?? ''));

    if (empty($email) || empty($codigo)) {
        echo json_encode(["success" => false, "message" => "Correo electrónico y código son requeridos."]);
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, token_recuperacion FROM usuarios WHERE email = :email AND expiracion_token > NOW() AND activo = 1 LIMIT 1");
        $stmt->execute([':email' => $email]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && !empty($usuario['token_recuperacion']) && hash_equals((string)$usuario['token_recuperacion'], $codigo)) {
            // Invalidar código de un solo uso inmediatamente y generar token criptográfico para el paso final
            $pdo->prepare("UPDATE usuarios SET token_recuperacion = NULL, expiracion_token = NULL WHERE id = ?")->execute([$usuario['id']]);
            $token = bin2hex(random_bytes(24));
            $_SESSION['recovery_token'] = $token;
            $_SESSION['recovery_user_id'] = (int)$usuario['id'];
            $_SESSION['recovery_expires'] = time() + 900;
            
            echo json_encode(["success" => true, "token" => $token]);
        } else {
            rateLimitFail('api_recovery');
            echo json_encode(["success" => false, "message" => "El código es incorrecto o ha expirado."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "message" => "Error de base de datos."]);
    }
    exit;
}

echo json_encode(["success" => false, "message" => "Acción no válida."]);
