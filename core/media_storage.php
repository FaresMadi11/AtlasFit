<?php
// core/media_storage.php — Almacenamiento permanente híbrido: Cloudflare R2 (preferido) + TiDB Cloud (fallback) + Caché local
declare(strict_types=1);

/**
 * Lee y normaliza la configuración de Cloudflare R2 desde variables de entorno.
 */
function r2Config(): array {
    return [
        'account_id' => trim((string)(function_exists('env') ? env('R2_ACCOUNT_ID', '') : (getenv('R2_ACCOUNT_ID') ?: ''))),
        'access_key' => trim((string)(function_exists('env') ? env('R2_ACCESS_KEY_ID', '') : (getenv('R2_ACCESS_KEY_ID') ?: ''))),
        'secret_key' => trim((string)(function_exists('env') ? env('R2_SECRET_ACCESS_KEY', '') : (getenv('R2_SECRET_ACCESS_KEY') ?: ''))),
        'bucket'     => trim((string)(function_exists('env') ? env('R2_BUCKET', '') : (getenv('R2_BUCKET') ?: ''))),
        'public_url' => rtrim(trim((string)(function_exists('env') ? env('R2_PUBLIC_URL', '') : (getenv('R2_PUBLIC_URL') ?: ''))), '/'),
    ];
}

/**
 * Indica si Cloudflare R2 está completamente configurado para recibir subidas.
 */
function r2IsConfigured(): bool {
    $cfg = r2Config();
    return $cfg['account_id'] !== ''
        && $cfg['access_key'] !== ''
        && $cfg['secret_key'] !== ''
        && $cfg['bucket'] !== '';
}

/**
 * Devuelve la URL pública en Cloudflare R2 para una ruta relativa, o null si no aplica.
 */
function r2PublicUrl(string $relativePath): ?string {
    $cfg = r2Config();
    if ($cfg['public_url'] === '') {
        return null;
    }
    $clean = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
    if ($clean === '') return null;
    $encoded = implode('/', array_map('rawurlencode', explode('/', $clean)));
    return $cfg['public_url'] . '/' . $encoded;
}

/**
 * Resuelve la URL final de cualquier media (URL externa, Cloudflare R2 o archivo local).
 */
function xpMediaUrl(?string $path): string {
    $clean = trim((string)$path);
    if ($clean === '') return '';
    if (preg_match('#^https?://#i', $clean) || str_starts_with($clean, 'data:')) {
        return $clean;
    }
    $rel = ltrim(str_replace('\\', '/', $clean), '/');
    $isComprobantePath = (
        str_starts_with($rel, 'uploads/comprobantes/') ||
        str_starts_with($rel, 'public/uploads/comprobantes/')
    );
    $isUserMedia = !$isComprobantePath && (
        str_starts_with($rel, 'uploads/') ||
        str_starts_with($rel, 'public/uploads/') ||
        str_starts_with($rel, 'assets/img/tienda/')
    );
    if ($isUserMedia && r2IsConfigured()) {
        $r2Url = r2PublicUrl($rel);
        if ($r2Url !== null) {
            return $r2Url;
        }
    }
    $base = defined('APP_URL') ? rtrim((string)APP_URL, '/') : '';
    return ($base !== '' ? ($base . '/') : '') . $rel;
}

/**
 * Firma y ejecuta una petición S3 AWS Signature Version 4 contra Cloudflare R2 (puro PHP).
 *
 * @return array{ok:bool,status:int,body:string,error:string}
 */
function r2SignAndSend(string $method, string $objectKey, string $body = '', string $contentType = '', string $cacheControl = ''): array {
    $cfg = r2Config();
    if (!r2IsConfigured()) {
        return ['ok' => false, 'status' => 0, 'body' => '', 'error' => 'R2 no configurado'];
    }

    $method     = strtoupper($method);
    $host       = $cfg['account_id'] . '.r2.cloudflarestorage.com';
    $cleanKey   = ltrim(str_replace('\\', '/', $objectKey), '/');
    $encodedKey = implode('/', array_map('rawurlencode', explode('/', $cleanKey)));
    $canonicalUri = '/' . rawurlencode($cfg['bucket']) . '/' . $encodedKey;

    $amzDate     = gmdate('Ymd\THis\Z');
    $dateStamp   = gmdate('Ymd');
    $region      = 'auto';
    $service     = 's3';
    $payloadHash = hash('sha256', $body);

    $headersMap = [
        'host'                 => $host,
        'x-amz-content-sha256' => $payloadHash,
        'x-amz-date'           => $amzDate,
    ];
    if ($contentType !== '') {
        $headersMap['content-type'] = $contentType;
    }
    if ($cacheControl !== '') {
        $headersMap['cache-control'] = $cacheControl;
    }
    ksort($headersMap);

    $canonicalHeaders = '';
    foreach ($headersMap as $k => $v) {
        $canonicalHeaders .= $k . ':' . trim($v) . "\n";
    }
    $signedHeaders = implode(';', array_keys($headersMap));

    $canonicalRequest = implode("\n", [
        $method,
        $canonicalUri,
        '', // query string
        $canonicalHeaders,
        $signedHeaders,
        $payloadHash,
    ]);

    $credentialScope = "{$dateStamp}/{$region}/{$service}/aws4_request";
    $stringToSign = implode("\n", [
        'AWS4-HMAC-SHA256',
        $amzDate,
        $credentialScope,
        hash('sha256', $canonicalRequest),
    ]);

    $kDate    = hash_hmac('sha256', $dateStamp, 'AWS4' . $cfg['secret_key'], true);
    $kRegion  = hash_hmac('sha256', $region, $kDate, true);
    $kService = hash_hmac('sha256', $service, $kRegion, true);
    $kSigning = hash_hmac('sha256', 'aws4_request', $kService, true);
    $signature = hash_hmac('sha256', $stringToSign, $kSigning);

    $authHeader = sprintf(
        'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
        $cfg['access_key'],
        $credentialScope,
        $signedHeaders,
        $signature
    );

    $httpHeaders = [
        'Authorization: ' . $authHeader,
        'x-amz-content-sha256: ' . $payloadHash,
        'x-amz-date: ' . $amzDate,
    ];
    if ($contentType !== '') {
        $httpHeaders[] = 'Content-Type: ' . $contentType;
    }
    if ($cacheControl !== '') {
        $httpHeaders[] = 'Cache-Control: ' . $cacheControl;
    }

    $url = 'https://' . $host . $canonicalUri;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        $opts = [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $httpHeaders,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 8,
        ];
        if ($method === 'PUT' || $method === 'POST') {
            $opts[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $opts);
        $respBody = (string)curl_exec($ch);
        $status   = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        return [
            'ok'     => ($err === '' && $status >= 200 && $status < 300),
            'status' => $status,
            'body'   => $respBody,
            'error'  => $err,
        ];
    }

    $httpHeaders[] = 'Content-Length: ' . strlen($body);
    $httpHeaders[] = 'Connection: close';
    $ctx = stream_context_create([
        'http' => [
            'method'        => $method,
            'header'        => implode("\r\n", $httpHeaders) . "\r\n",
            'content'       => ($method === 'PUT' || $method === 'POST') ? $body : '',
            'timeout'       => 25,
            'ignore_errors' => true,
        ],
    ]);
    $respBody = @file_get_contents($url, false, $ctx);
    $status = 0;
    $respHeaders = function_exists('http_get_last_response_headers') ? (http_get_last_response_headers() ?? []) : ($http_response_header ?? []);
    if (!empty($respHeaders) && preg_match('/HTTP\/\d\.\d\s+(\d+)/', (string)($respHeaders[0] ?? ''), $m)) {
        $status = (int)$m[1];
    }
    return [
        'ok'     => ($respBody !== false && $status >= 200 && $status < 300),
        'status' => $status,
        'body'   => (string)($respBody ?: ''),
        'error'  => $respBody === false ? 'Error HTTP stream hacia R2' : '',
    ];
}

function r2UploadBytes(string $objectKey, string $bytes, string $mimeType): bool {
    $res = r2SignAndSend('PUT', $objectKey, $bytes, $mimeType, 'public, max-age=31536000, immutable');
    if (!$res['ok']) {
        error_log('[Xprod Fit R2] Fallo PUT ' . $objectKey . ' (HTTP ' . $res['status'] . '): ' . ($res['error'] ?: substr($res['body'], 0, 200)));
    }
    return $res['ok'];
}

function r2DeleteObject(string $objectKey): bool {
    $res = r2SignAndSend('DELETE', $objectKey);
    return $res['ok'] || $res['status'] === 404;
}

/**
 * Detecta si los bytes corresponden a un GIF o WebP animado para NO aplanarlo con GD.
 */
function esImagenAnimada(string $rawBytes, string $ext): bool {
    $ext = strtolower($ext);
    if ($ext === 'webp') {
        return str_contains(substr($rawBytes, 0, 200), 'ANIM');
    }
    if ($ext === 'gif') {
        return preg_match_all('#\x00\x21\xF9\x04.{4}\x00[\x2C\x21]#s', $rawBytes) > 1;
    }
    return false;
}

/**
 * Garantiza que la tabla archivos_media exista en la base de datos.
 */
function ensureMediaTable(PDO $db): void {
    static $checked = false;
    if ($checked) return;
    $db->exec("
        CREATE TABLE IF NOT EXISTS archivos_media (
            ruta VARCHAR(255) NOT NULL PRIMARY KEY,
            mime_type VARCHAR(60) NOT NULL DEFAULT 'image/jpeg',
            contenido MEDIUMBLOB NOT NULL,
            es_comprobante TINYINT(1) NOT NULL DEFAULT 0,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_comprobante_fecha (es_comprobante, creado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    $checked = true;
}

/**
 * Redimensiona y comprime una imagen en memoria con GD para ahorrar ~90-95% de espacio en TiDB Cloud.
 * Retorna ['bytes' => string, 'mime' => string]
 */
function comprimirImagenGD(string $rawBytes, string $ext, int $maxDim = 800, int $quality = 78): array {
    $ext = strtolower($ext);
    if ($ext === 'pdf') {
        return ['bytes' => $rawBytes, 'mime' => 'application/pdf'];
    }
    if ($ext === 'mp4') {
        return ['bytes' => $rawBytes, 'mime' => 'video/mp4'];
    }
    if ($ext === 'webm') {
        return ['bytes' => $rawBytes, 'mime' => 'video/webm'];
    }
    if ($ext === 'mov') {
        return ['bytes' => $rawBytes, 'mime' => 'video/quicktime'];
    }
    // Preservar GIF y WebP animados intactos sin perder sus frames
    if (($ext === 'gif' || $ext === 'webp') && esImagenAnimada($rawBytes, $ext)) {
        return ['bytes' => $rawBytes, 'mime' => $ext === 'gif' ? 'image/gif' : 'image/webp'];
    }

    if (!function_exists('imagecreatefromstring')) {
        $mime = match ($ext) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            default => 'image/jpeg',
        };
        return ['bytes' => $rawBytes, 'mime' => $mime];
    }

    $src = @imagecreatefromstring($rawBytes);
    if (!$src) {
        return ['bytes' => '', 'mime' => ''];
    }

    $w = imagesx($src);
    $h = imagesy($src);

    if ($w > $maxDim || $h > $maxDim) {
        if ($w >= $h) {
            $newW = $maxDim;
            $newH = max(1, (int)round(($h * $maxDim) / $w));
        } else {
            $newH = $maxDim;
            $newW = max(1, (int)round(($w * $maxDim) / $h));
        }
        $dst = imagecreatetruecolor($newW, $newH);
        if ($ext === 'webp' || $ext === 'png') {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
            $trans = imagecolorallocatealpha($dst, 0, 0, 0, 127);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $trans);
        } else {
            $white = imagecolorallocate($dst, 255, 255, 255);
            imagefilledrectangle($dst, 0, 0, $newW, $newH, $white);
        }
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $w, $h);
        $src = $dst;
    }

    ob_start();
    if (function_exists('imagewebp') && $ext === 'webp') {
        imagepalettetotruecolor($src);
        imagealphablending($src, true);
        imagesavealpha($src, true);
        imagewebp($src, null, $quality);
        $mime = 'image/webp';
    } else {
        imagejpeg($src, null, $quality);
        $mime = 'image/jpeg';
    }
    $compressed = (string)ob_get_clean();
    unset($src);

    if ($compressed === '') {
        return ['bytes' => $rawBytes, 'mime' => $ext === 'webp' ? 'image/webp' : 'image/jpeg'];
    }

    return ['bytes' => $compressed, 'mime' => $mime];
}

/**
 * Comprime y guarda un archivo en disco local + Cloudflare R2 (si está configurado) o TiDB Cloud (respaldo).
 */
function guardarMediaPermanente(
    string $rawBytes,
    string $relativePath,
    int $maxDim = 800,
    int $quality = 78,
    bool $esComprobante = false
): bool {
    $relativePath = ltrim(str_replace('\\', '/', $relativePath), '/');
    $ext = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

    $processed = comprimirImagenGD($rawBytes, $ext, $maxDim, $quality);
    if ($processed['bytes'] === '') {
        return false;
    }
    $fullPath  = BASE_DIR . '/' . $relativePath;
    $dir       = dirname($fullPath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }

    // 1. Guardar en caché de disco local
    $okDisk = (@file_put_contents($fullPath, $processed['bytes']) !== false);

    // 2. Prioridad 1: Cloudflare R2 (solo media pública; los comprobantes privados se sirven vía PHP autenticado)
    $isPrivateReceipt = $esComprobante
        || str_starts_with($relativePath, 'uploads/comprobantes/')
        || str_starts_with($relativePath, 'public/uploads/comprobantes/');
    if (!$isPrivateReceipt && r2IsConfigured()) {
        if (r2UploadBytes($relativePath, $processed['bytes'], $processed['mime'])) {
            return true;
        }
    }

    // 3. Prioridad 2 (Fallback / Comprobantes privados): Respaldar en TiDB Cloud para persistencia ante redeploys en Render
    try {
        $db = getDB();
        ensureMediaTable($db);
        $stmt = $db->prepare("
            INSERT INTO archivos_media (ruta, mime_type, contenido, es_comprobante, creado_en)
            VALUES (?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                mime_type = VALUES(mime_type),
                contenido = VALUES(contenido),
                es_comprobante = VALUES(es_comprobante),
                creado_en = NOW()
        ");
        $stmt->execute([
            $relativePath,
            $processed['mime'],
            $processed['bytes'],
            $isPrivateReceipt ? 1 : 0,
        ]);
        return true;
    } catch (\Throwable $e) {
        error_log('[Xprod Fit Media] Error guardando en BD: ' . $e->getMessage());
        return $okDisk;
    }
}

/**
 * Elimina un archivo del disco local, Cloudflare R2 (si está activo) y TiDB Cloud.
 */
function eliminarMediaPermanente(?string $relativePath): void {
    $relativePath = ltrim(str_replace('\\', '/', trim((string)$relativePath)), '/');
    if ($relativePath === '' || str_contains($relativePath, '..')) return;

    $fullPath = BASE_DIR . '/' . $relativePath;
    if (file_exists($fullPath)) {
        @unlink($fullPath);
    }

    if (r2IsConfigured()) {
        try {
            r2DeleteObject($relativePath);
        } catch (\Throwable $e) {
            error_log('[Xprod Fit R2] Error eliminando objeto: ' . $e->getMessage());
        }
    }

    try {
        $db = getDB();
        ensureMediaTable($db);
        $db->prepare("DELETE FROM archivos_media WHERE ruta = ?")->execute([$relativePath]);
    } catch (\Throwable $e) {
        error_log('[Xprod Fit Media] Error eliminando de BD: ' . $e->getMessage());
    }
}

/**
 * Restaura y sirve un archivo desde TiDB Cloud o redirige a Cloudflare R2 si no existe en disco local.
 * Incluye soporte HTTP Range (206 Partial Content) para reproducción de videos MP4/WebM en Safari/iOS.
 */
function intentarServirMediaRespaldado(string $route): bool {
    $route = ltrim(str_replace('\\', '/', trim($route)), '/');
    if ($route === '' || str_contains($route, '..')) return false;

    $isMediaRoute = (
        str_starts_with($route, 'uploads/') ||
        str_starts_with($route, 'public/uploads/') ||
        str_starts_with($route, 'assets/img/tienda/')
    );
    if (!$isMediaRoute) return false;

    $isComprobante = (
        str_starts_with($route, 'uploads/comprobantes/') ||
        str_starts_with($route, 'public/uploads/comprobantes/')
    );

    // Comprobantes privados: exigir sesión autenticada y verificar propiedad (atleta dueño, su entrenador o Super Admin)
    if ($isComprobante) {
        $uid = (int)($_SESSION['user_id'] ?? 0);
        $rol = (string)($_SESSION['rol'] ?? '');
        if ($uid <= 0 || !in_array($rol, ['admin', 'entrenador', 'cliente'], true)) {
            http_response_code(403);
            exit('Acceso denegado.');
        }

        try {
            $db = getDB();
            if ($rol !== 'admin') {
                $altRoute = str_starts_with($route, 'public/') ? substr($route, 7) : ('public/' . $route);
                if ($rol === 'cliente') {
                    $cid = (int)($_SESSION['cliente_id'] ?? 0);
                    $stOwn = $db->prepare("SELECT 1 FROM pagos WHERE cliente_id = ? AND (comprobante_img = ? OR comprobante_img = ?) LIMIT 1");
                    $stOwn->execute([$cid, $route, $altRoute]);
                } else {
                    $stOwn = $db->prepare(
                        "SELECT 1 FROM pagos p JOIN clientes c ON c.id = p.cliente_id
                         WHERE c.entrenador_id = ? AND (p.comprobante_img = ? OR p.comprobante_img = ?) LIMIT 1"
                    );
                    $stOwn->execute([$uid, $route, $altRoute]);
                }
                if (!$stOwn->fetchColumn()) {
                    http_response_code(403);
                    exit('Sin permisos para ver este comprobante.');
                }
            }

            $diskPath = BASE_DIR . '/' . $route;
            $bytes = null;
            $mime = null;
            if (is_file($diskPath)) {
                $bytes = (string)@file_get_contents($diskPath);
                if (function_exists('finfo_open')) {
                    $fi = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = $fi ? (string)finfo_buffer($fi, $bytes) : null;
                    if ($fi) finfo_close($fi);
                }
            }
            if ($bytes === null || $bytes === '') {
                ensureMediaTable($db);
                $stmt = $db->prepare("SELECT mime_type, contenido FROM archivos_media WHERE ruta = ? LIMIT 1");
                $stmt->execute([$route]);
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['contenido'])) {
                    $bytes = (string)$row['contenido'];
                    $mime = (string)($row['mime_type'] ?: 'image/jpeg');
                }
            }
            if ($bytes === null || $bytes === '') {
                http_response_code(404);
                exit('Comprobante no encontrado.');
            }
            header('Content-Type: ' . ($mime ?: 'application/octet-stream'));
            header('Cache-Control: private, no-store, max-age=0');
            header('X-Content-Type-Options: nosniff');
            header('Content-Length: ' . strlen($bytes));
            echo $bytes;
            exit;
        } catch (\Throwable $e) {
            error_log('[Xprod Fit Media] Error sirviendo comprobante protegido: ' . $e->getMessage());
            http_response_code(500);
            exit('Error al servir comprobante.');
        }
    }

    try {
        $db = getDB();
        ensureMediaTable($db);
        $stmt = $db->prepare("SELECT mime_type, contenido FROM archivos_media WHERE ruta = ? LIMIT 1");
        $stmt->execute([$route]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || empty($row['contenido'])) {
            // Si no está en TiDB pero R2 está activo, redirigir al CDN público de Cloudflare R2
            if (r2IsConfigured()) {
                $r2Url = r2PublicUrl($route);
                if ($r2Url !== null) {
                    header('Location: ' . $r2Url, true, 302);
                    exit;
                }
            }
            return false;
        }

        // Auto-reparar (Self-Healing): volver a escribir en disco para que Apache lo sirva directo en la próxima petición
        $fullPath = BASE_DIR . '/' . $route;
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($fullPath, $row['contenido']);

        $bytes = (string)$row['contenido'];
        $total = strlen($bytes);
        $mime  = $row['mime_type'] ?: 'image/jpeg';

        header('Content-Type: ' . $mime);
        header('Cache-Control: public, max-age=2592000');
        header('Accept-Ranges: bytes');

        if (!empty($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', (string)$_SERVER['HTTP_RANGE'], $m)) {
            $start = $m[1] !== '' ? (int)$m[1] : 0;
            $end   = $m[2] !== '' ? min((int)$m[2], $total - 1) : ($total - 1);
            if ($start <= $end && $start < $total) {
                $length = $end - $start + 1;
                http_response_code(206);
                header("Content-Range: bytes {$start}-{$end}/{$total}");
                header('Content-Length: ' . $length);
                echo substr($bytes, $start, $length);
                exit;
            }
        }

        header('Content-Length: ' . $total);
        echo $bytes;
        exit;
    } catch (\Throwable $e) {
        error_log('[Xprod Fit Media] Error sirviendo media respaldado: ' . $e->getMessage());
        return false;
    }
}

/**
 * Purga automáticamente los comprobantes de pago que tengan 10 o más días de antigüedad
 * para liberar espacio en los 5 GB de TiDB Cloud.
 */
function purgarComprobantesVencidos(PDO $db, int $dias = 10): int {
    try {
        ensureMediaTable($db);
        $dias = max(1, $dias);
        $stmt = $db->prepare("
            SELECT ruta FROM archivos_media
            WHERE es_comprobante = 1
              AND creado_en <= DATE_SUB(NOW(), INTERVAL ? DAY)
            LIMIT 50
        ");
        $stmt->execute([$dias]);
        $rutas = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($rutas)) return 0;

        $delMedia = $db->prepare("DELETE FROM archivos_media WHERE ruta = ?");
        $updPago  = $db->prepare("UPDATE pagos SET comprobante_img = NULL WHERE comprobante_img = ?");

        foreach ($rutas as $ruta) {
            $fullPath = BASE_DIR . '/' . $ruta;
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
            $delMedia->execute([$ruta]);
            $updPago->execute([$ruta]);
        }

        return count($rutas);
    } catch (\Throwable $e) {
        error_log('[Xprod Fit Media] Error purgando comprobantes: ' . $e->getMessage());
        return 0;
    }
}
