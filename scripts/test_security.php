<?php
declare(strict_types=1);

// Pruebas locales de protección básica: no usan datos reales ni envían correo.
if (PHP_SAPI !== 'cli' || getenv('CI_SMOKE') !== '1') {
    exit(2);
}

require_once __DIR__ . '/../Avance2/security_rate_limit.php';
require_once __DIR__ . '/../Avance2/csrf.php';
ambienteStartSession();
$_SERVER['REMOTE_ADDR'] = '198.51.100.42';
$action = 'test' . getmypid();
if (!ambienteAllowRequest($action, 2, 60)
    || !ambienteAllowRequest($action, 2, 60)
    || ambienteAllowRequest($action, 2, 60)) {
    throw new RuntimeException('Límite de solicitudes no aplicado correctamente.');
}
echo "Límite por IP: OK\n";

$settings = session_get_cookie_params();
if ($settings['httponly'] !== true || $settings['samesite'] !== 'Lax'
    || ini_get('session.use_strict_mode') !== '1') {
    throw new RuntimeException('La política de cookies no cumple las expectativas.');
}
echo "Cookies de sesión: OK\n";
