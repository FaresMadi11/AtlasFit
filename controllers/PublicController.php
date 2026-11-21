<?php
// controllers/PublicController.php
declare(strict_types=1);

// Endpoint ligero de Keep-Alive / Health Check para evitar hibernación en Render y TiDB Cloud
if ($action === 'ping' || $action === 'health') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    try {
        $db = getDB();
        $db->query('SELECT 1');
        echo json_encode(['status' => 'ok', 'db' => 'connected', 'ts' => time()]);
    } catch (\Throwable $e) {
        http_response_code(503);
        echo json_encode(['status' => 'error', 'db' => 'disconnected']);
    }
    exit;
}

// Versión ligera de build (0 consultas BD, detecta deploys nuevos al instante en PWA)
if ($action === 'version') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    echo json_encode(['v' => xpAppBuildVersion()]);
    exit;
}

// Manifest dinámico PWA: siempre sin caché HTTP y con ?v= automático en los íconos
if ($action === 'manifest') {
    header('Content-Type: application/manifest+json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    $v192  = xpAssetVer('assets/img/icon-192.png');
    $v512  = xpAssetVer('assets/img/icon-512.png');
    $vMask = xpAssetVer('assets/img/icon-maskable-512.png');
    echo json_encode([
        'id'               => './',
        'name'             => 'Atlas Fit',
        'short_name'       => 'Atlas Fit',
        'description'      => 'Sistema de gestión para entrenadores y atletas',
        'start_url'        => './',
        'scope'            => './',
        'display'          => 'standalone',
        'orientation'      => 'portrait',
        'background_color' => '#02040a',
        'theme_color'      => '#02040a',
        'categories'       => ['fitness', 'health', 'sports'],
        'icons'            => [
            [
                'src'     => './assets/img/icon-192.png?v=' . $v192,
                'sizes'   => '192x192',
                'type'    => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src'     => './assets/img/icon-512.png?v=' . $v512,
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'any',
            ],
            [
                'src'     => './assets/img/icon-maskable-512.png?v=' . $vMask,
                'sizes'   => '512x512',
                'type'    => 'image/png',
                'purpose' => 'maskable',
            ],
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

if ($action === 'producto') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        http_response_code(404);
        exit('Producto no encontrado.');
    }

    $db = getDB();
    $stmt = $db->prepare(
        "SELECT p.id, p.seccion_id, p.nombre, p.descripcion, p.precio, p.imagen_path, p.activo, s.entrenador_id
         FROM tienda_productos p
         JOIN tienda_secciones s ON s.id = p.seccion_id
         WHERE p.id = ? AND p.activo = 1"
    );
    $stmt->execute([$id]);
    $producto = $stmt->fetch();

    if (!$producto) {
        http_response_code(404);
        exit('Producto no encontrado.');
    }

    $entStoreId = (int)($producto['entrenador_id'] ?? 0);
    if ($entStoreId > 0) {
        $memStore = obtenerEstadoMembresiaEntrenador($entStoreId);
        if (empty($memStore['tienda_activa']) || !empty($memStore['bloqueado'])) {
            http_response_code(404);
            exit('Esta tienda no se encuentra disponible.');
        }
    }

    require BASE_DIR . '/views/public/producto.php';
    exit;
}

http_response_code(404);
exit('Ruta pública no encontrada.');
