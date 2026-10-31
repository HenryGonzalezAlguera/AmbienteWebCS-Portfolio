<?php
declare(strict_types=1);

/**
 * Protección CSRF de operaciones que modifican datos.
 * Tokens vinculados a la sesión PHP y verificados en el servidor.
 */
function ambienteStartSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Evita identificadores de sesión no emitidos por el servidor.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');

    // No se confía en encabezados X-Forwarded-* del navegador.
    // Si el hosting termina TLS en un proxy, configurar APP_SECURE_COOKIES=1.
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || ((string) ($_SERVER['SERVER_PORT'] ?? '') === '443')
        || getenv('APP_SECURE_COOKIES') === '1';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function ambienteWriteToken(): string
{
    ambienteStartSession();
    if (!isset($_SESSION['csrf_write']) || !is_string($_SESSION['csrf_write'])
        || strlen($_SESSION['csrf_write']) !== 64) {
        $_SESSION['csrf_write'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_write'];
}

function ambienteRequireWriteToken(): void
{
    $expected = ambienteWriteToken();
    $actual = $_POST['csrf_token'] ?? null;
    if (!is_string($actual) || !hash_equals($expected, $actual)) {
        http_response_code(403);
        header('Content-Type: text/html; charset=utf-8');
        exit('Solicitud no válida o formulario caducado. <a href="index.html">Volver al inicio</a>.');
    }
}
