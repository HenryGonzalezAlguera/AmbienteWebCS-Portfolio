<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.html', true, 303);
    exit;
}

require_once __DIR__ . '/security_rate_limit.php';
if (!ambienteAllowRequest('login', 20, 900)) {
    header('Retry-After: 900');
    http_response_code(429);
    exit('Demasiados intentos. Vuelve a intentarlo más tarde.');
}

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
$password = (string) ($_POST['password'] ?? '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    header('Location: login.html?error=credentials', true, 303);
    exit;
}

require_once __DIR__ . '/db_config.php';
try {
    $stmt = $conn->prepare('SELECT id, nombre, email, password FROM usuarios WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, (string) $user['password'])) {
        header('Location: login.html?error=credentials', true, 303);
        exit;
    }

    require_once __DIR__ . '/csrf.php';
    ambienteStartSession();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_email'] = (string) $user['email'];
    $_SESSION['user_name'] = (string) $user['nombre'];
    header('Location: index.html', true, 303);
    exit;
} catch (mysqli_sql_exception $e) {
    error_log('Error de autenticación: ' . $e->getMessage());
    header('Location: login.html?error=server', true, 303);
    exit;
}
