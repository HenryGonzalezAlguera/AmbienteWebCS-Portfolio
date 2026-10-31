<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

require_once __DIR__ . '/csrf.php';
ambienteStartSession();

$authenticated = isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;

if ($authenticated && empty($_SESSION['csrf_logout'])) {
    $_SESSION['csrf_logout'] = bin2hex(random_bytes(32));
}

echo json_encode([
    'authenticated' => $authenticated,
    'display_name' => $authenticated ? (string) ($_SESSION['user_name'] ?? '') : '',
    'logout_csrf' => $authenticated ? (string) $_SESSION['csrf_logout'] : '',
    'write_csrf' => ambienteWriteToken(),
], JSON_UNESCAPED_UNICODE);
