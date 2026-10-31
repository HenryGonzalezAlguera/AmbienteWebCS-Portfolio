<?php
declare(strict_types=1);

// Configuración local opcional (excluida mediante .gitignore).
$localPath = __DIR__ . '/db_config.local.php';
$local = is_file($localPath) ? require $localPath : [];
if (!is_array($local)) {
    error_log('Configuración MySQL inválida.');
    http_response_code(503);
    exit('La base de datos no está configurada.');
}

$setting = static function (string $key, string $default = '') use ($local): string {
    $value = getenv($key);
    return $value !== false ? (string) $value : (string) ($local[$key] ?? $default);
};

$host = $setting('DB_HOST', '127.0.0.1');
$port = (int) $setting('DB_PORT', '3306');
$name = $setting('DB_NAME', 'reduccion_desperdicio');
$user = $setting('DB_USER');
$pass = $setting('DB_PASSWORD');

if ($user === '' || $name === '' || $port < 1 || $port > 65535) {
    error_log('Faltan parámetros de conexión MySQL.');
    http_response_code(503);
    exit('La base de datos no está configurada.');
}

try {
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $conn = new mysqli($host, $user, $pass, $name, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Error de conexión MySQL: ' . $exception->getMessage());
    http_response_code(503);
    exit('No fue posible conectar con la base de datos.');
}
