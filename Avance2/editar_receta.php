<?php
declare(strict_types=1);
require_once __DIR__ . '/csrf.php';
ambienteStartSession();

$userId = (int) ($_SESSION['user_id'] ?? 0);
if ($userId < 1) {
    header('Location: login.html', true, 303);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method !== 'GET' && $method !== 'POST') {
    header('Allow: GET, POST');
    http_response_code(405);
    exit('Método no permitido.');
}

$id = filter_var(($method === 'POST' ? ($_POST['id'] ?? null) : ($_GET['id'] ?? null)), FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    http_response_code(400);
    exit('Selecciona una receta válida.');
}

if ($method === 'POST') {
    ambienteRequireWriteToken();
}

require_once __DIR__ . '/db_config.php';
$receta = null;
$error = '';

try {
    // El propietario se comprueba en SQL, no en campos modificables del navegador.
    $stmt = $conn->prepare(
        'SELECT id, nombre, ingredientes, instrucciones FROM recetas WHERE id = ? AND usuario_id = ? LIMIT 1'
    );
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $receta = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$receta) {
        http_response_code(404);
        $error = 'La receta no existe o no puedes modificarla.';
    } elseif ($method === 'POST') {
        $nombre = trim((string) ($_POST['nombreReceta'] ?? ''));
        $ingredientes = trim((string) ($_POST['ingredientes'] ?? ''));
        $instrucciones = trim((string) ($_POST['instrucciones'] ?? ''));

        // Los límites también se validan en el backend; los maxlength HTML no bastan.
        if ($nombre === '' || strlen($nombre) > 180 || $ingredientes === '' || $instrucciones === ''
            || strlen($ingredientes) > 15000 || strlen($instrucciones) > 15000) {
            http_response_code(422);
            $error = 'Completa todos los campos e indica un nombre válido.';
            $receta['nombre'] = $nombre;
            $receta['ingredientes'] = $ingredientes;
            $receta['instrucciones'] = $instrucciones;
        } else {
            $stmt = $conn->prepare(
                'UPDATE recetas SET nombre = ?, ingredientes = ?, instrucciones = ? WHERE id = ? AND usuario_id = ?'
            );
            $stmt->bind_param('sssii', $nombre, $ingredientes, $instrucciones, $id, $userId);
            $stmt->execute();
            $stmt->close();
            header('Location: recetas.php?id=' . $id . '&updated=1', true, 303);
            exit;
        }
    }
} catch (mysqli_sql_exception $e) {
    error_log('Error al editar receta: ' . $e->getMessage());
    http_response_code(500);
    $error = 'Ocurrió un error al consultar o actualizar la receta.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar receta | AmbienteWebCS</title>
    <link rel="stylesheet" href="interior.css">
</head>
<body class="site-app"><div class="app-shell">
<header class="site-header">
    <a class="brand" href="index.html"><span class="brand-mark">🌱</span>Ambiente<span class="brand-accent">WebCS</span></a>
    <nav class="site-nav"><a href="recetas.html">Recetas</a><a href="index.html">Inicio</a></nav>
</header>
<main class="page-container page-narrow">
    <a class="return-link" href="recetas.php?id=<?= $id ?>">← Volver a la receta</a>
    <div class="page-intro">
        <p class="eyebrow">MI COLECCIÓN</p>
        <h1>Editar receta</h1>
        <p class="lede">Actualiza los ingredientes y pasos de tu propia receta.</p>
    </div>
    <section class="panel">
        <?php if ($error !== ''): ?>
            <div class="alert error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($receta): ?>
        <form class="ui-form" method="post" action="editar_receta.php">
            <input type="hidden" name="id" value="<?= (int) $receta['id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(ambienteWriteToken(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="form-field">
                <label for="nombreReceta">Nombre de la receta</label>
                <input id="nombreReceta" type="text" name="nombreReceta" maxlength="180" required value="<?= htmlspecialchars((string) $receta['nombre'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="form-field">
                <label for="ingredientes">Ingredientes</label>
                <textarea id="ingredientes" name="ingredientes" maxlength="15000" required><?= htmlspecialchars((string) $receta['ingredientes'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <div class="form-field">
                <label for="instrucciones">Preparación</label>
                <textarea id="instrucciones" name="instrucciones" maxlength="15000" required><?= htmlspecialchars((string) $receta['instrucciones'], ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
            <button class="primary" type="submit">Guardar cambios →</button>
        </form>
        <?php endif; ?>
    </section>
</main>
<footer class="site-footer">© AmbienteWebCS · Proyecto educativo</footer>
</div></body></html>
