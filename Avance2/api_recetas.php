<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
require_once __DIR__ . '/db_config.php';
try {
    $result = $conn->query('SELECT id, nombre FROM recetas ORDER BY id DESC LIMIT 100');
    echo json_encode($result->fetch_all(MYSQLI_ASSOC), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (Throwable $e) {
    error_log('Error al consultar recetas: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo consultar recetas']);
}
