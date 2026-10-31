<?php
declare(strict_types=1);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: nuevaReceta.html', true, 303);
    exit;
}
require_once __DIR__ . '/csrf.php';
ambienteStartSession();
if (!isset($_SESSION['user_id']) || (int) $_SESSION['user_id'] < 1) {
    http_response_code(401);
    header('Content-Type: text/html; charset=utf-8');
    exit('Inicia sesión para publicar recetas. <a href="login.html">Ingresar</a>.');
}
ambienteRequireWriteToken();

$nombre = trim((string) ($_POST['nombreReceta'] ?? ''));
$ingredientes = trim((string) ($_POST['ingredientes'] ?? ''));
$instrucciones = trim((string) ($_POST['instrucciones'] ?? ''));
if ($nombre === '' || $ingredientes === '' || $instrucciones === '' || strlen($nombre) > 180) {
    http_response_code(422);
    exit('Complete todos los campos de la receta. <a href="nuevaReceta.html">Volver</a>');
}

require_once __DIR__ . '/db_config.php';
try {
    $authorId = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare('INSERT INTO recetas (usuario_id, nombre, instrucciones, ingredientes) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('isss', $authorId, $nombre, $instrucciones, $ingredientes);
    $stmt->execute();
    $id = $conn->insert_id;
    $stmt->close();
    header('Location: recetas.php?id=' . urlencode((string) $id), true, 303);
    exit;
} catch (mysqli_sql_exception $e) {
    error_log('Error al crear receta: ' . $e->getMessage());
    http_response_code(500);
    exit('No se pudo guardar la receta. <a href="nuevaReceta.html">Volver</a>');
}
