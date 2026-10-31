<?php
declare(strict_types=1);
require_once __DIR__ . '/csrf.php';
ambienteStartSession();

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Eliminación no permitida mediante enlaces.');
}
$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId < 1) {
    http_response_code(401);
    exit('Debes iniciar sesión para administrar tus recetas.');
}
ambienteRequireWriteToken();

$id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Identificador de receta inválido.');
}

require_once __DIR__ . '/db_config.php';
try {
    // Una receta sin propietario o de otro usuario nunca se elimina.
    $stmt = $conn->prepare('DELETE FROM recetas WHERE id = ? AND usuario_id = ?');
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $deleted = $stmt->affected_rows;
    $stmt->close();
    if ($deleted !== 1) {
        http_response_code(404);
        exit('La receta no existe o no puedes eliminarla.');
    }
    header('Location: recetas_populares.php?deleted=1', true, 303);
    exit;
} catch (mysqli_sql_exception $e) {
    error_log('Error al eliminar receta: ' . $e->getMessage());
    http_response_code(500);
    exit('No fue posible eliminar la receta.');
}
