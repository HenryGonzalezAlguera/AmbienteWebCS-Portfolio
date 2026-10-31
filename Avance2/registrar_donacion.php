<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método no permitido.');
}

require_once __DIR__ . '/csrf.php';
ambienteStartSession();
if ((int) ($_SESSION['user_id'] ?? 0) < 1) {
    http_response_code(401);
    header('Content-Type: text/html; charset=utf-8');
    exit('Debes iniciar sesión para registrar una donación. <a href="login.html">Iniciar sesión</a>.');
}
ambienteRequireWriteToken();

$nombre = trim((string) ($_POST['nombre_donante'] ?? ''));
$alimento = trim((string) ($_POST['alimento'] ?? ''));
$cantidad = filter_var($_POST['cantidad'] ?? null, FILTER_VALIDATE_INT);
$punto = trim((string) ($_POST['punto_recoleccion'] ?? ''));

$error = '';
if ($nombre === '' || $alimento === '' || $punto === '' || $cantidad === false || $cantidad <= 0
    || strlen($nombre) > 240 || strlen($alimento) > 360 || strlen($punto) > 360) {
    http_response_code(422);
    $error = 'Completa los datos e indica una cantidad válida.';
} else {
    require_once __DIR__ . '/db_config.php';
    try {
        $fecha = date('Y-m-d');
        $stmt = $conn->prepare('INSERT INTO donaciones (nombre_donante, alimento, cantidad, fecha, punto_recoleccion) VALUES (?, ?, ?, ?, ?)');
        $stmt->bind_param('ssiss', $nombre, $alimento, $cantidad, $fecha, $punto);
        $stmt->execute();
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        error_log('Error al registrar donación: ' . $e->getMessage());
        http_response_code(500);
        $error = 'No se pudo registrar la donación.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Resultado de donación | AmbienteWebCS</title><link rel="stylesheet" href="interior.css"></head>
<body class="site-app"><div class="app-shell">
<header class="site-header"><a class="brand" href="index.html"><span class="brand-mark">🌱</span>Ambiente<span class="brand-accent">WebCS</span></a><nav class="site-nav"><a href="index.html">Inicio</a><a href="recetas.html">Recetas</a><a href="donacion.html">Donaciones</a></nav></header>
<main class="page-container page-narrow"><a class="breadcrumb" href="donacion.html">← Volver a donaciones</a><div class="page-intro"><p class="eyebrow">REGISTRO DE ALIMENTOS</p><h1><?= $error === '' ? 'Registro completado' : 'No se pudo completar' ?></h1></div>
<div class="panel">
<?php if ($error === ''): ?>
    <div class="alert" role="status">Donación registrada con éxito.</div>
    <p>La información fue guardada en tu base de datos de demostración.</p>
    <p class="form-hint">Esta aplicación no coordina entregas ni confirma centros de donación reales.</p>
<?php else: ?>
    <div class="alert error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<div class="actions"><a class="button-primary" href="donacion.html">Registrar otra donación →</a><a class="button-secondary" href="index.html">Volver al inicio</a></div>
</div></main><footer class="site-footer">© AmbienteWebCS · Proyecto educativo</footer></div></body></html>
