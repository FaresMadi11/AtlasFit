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
        $purgados = purgarComprobantesVencidos($db, 10);
        echo json_encode(['status' => 'ok', 'db' => 'connected', 'purged_receipts' => $purgados, 'ts' => time()]);
    } catch (\Throwable $e) {
        http_response_code(503);
        echo json_encode(['status' => 'error', 'db' => 'disconnected']);
    }
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
