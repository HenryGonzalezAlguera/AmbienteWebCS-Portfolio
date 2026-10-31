<?php
declare(strict_types=1);

// El cierre de sesión modifica el estado: usar POST y validar un token CSRF.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Utiliza el botón Cerrar sesión de la página principal.');
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$expected = $_SESSION['csrf_logout'] ?? null;
$provided = (string) ($_POST['csrf_logout'] ?? '');

if (!is_string($expected) || $expected === '' || !hash_equals($expected, $provided)) {
    http_response_code(403);
    exit('La solicitud de cierre de sesión no es válida. Vuelve al inicio.');
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => (bool) $params['secure'],
        'httponly' => (bool) $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
header('Location: login.html?logout=1', true, 303);
exit;
