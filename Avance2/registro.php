<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: registro.html', true, 303);
    exit;
}

require_once __DIR__ . '/security_rate_limit.php';
if (!ambienteAllowRequest('register', 8, 900)) {
    header('Retry-After: 900');
    http_response_code(429);
    exit('Demasiados intentos. Vuelve a intentarlo más tarde.');
}

$nombre = trim((string) ($_POST['nombre'] ?? ''));
$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
$confirmacion = (string) ($_POST['confirmar_password'] ?? '');

if ($nombre === '' || strlen($nombre) > 240 || !filter_var($email, FILTER_VALIDATE_EMAIL)
    || strlen($password) < 8 || strlen($password) > 72 || $password !== $confirmacion) {
    header('Location: registro.html?error=invalid', true, 303);
    exit;
}

require_once __DIR__ . '/db_config.php';
try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $nombre, $email, $hash);
    $stmt->execute();
    $stmt->close();
    header('Location: login.html?registered=1', true, 303);
    exit;
} catch (mysqli_sql_exception $e) {
    if ((int) $e->getCode() === 1062) {
        header('Location: registro.html?error=duplicate', true, 303);
        exit;
    }
    error_log('Error en registro: ' . $e->getMessage());
    header('Location: registro.html?error=server', true, 303);
    exit;
}
