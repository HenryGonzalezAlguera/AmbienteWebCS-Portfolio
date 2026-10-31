<?php
declare(strict_types=1);
require_once __DIR__ . '/csrf.php';
ambienteStartSession();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$receta = null;
$error = '';
$isOwner = false;
if (!$id || $id < 1) {
    http_response_code(400);
    $error = 'Selecciona una receta válida.';
} else {
    require_once __DIR__ . '/db_config.php';
    try {
        $stmt = $conn->prepare('SELECT id, usuario_id, nombre, ingredientes, instrucciones FROM recetas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $receta = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$receta) {
            http_response_code(404);
            $error = 'No se encontró la receta solicitada.';
        } else {
            $isOwner = $receta['usuario_id'] !== null
                && (int) $receta['usuario_id'] === (int) ($_SESSION['user_id'] ?? 0)
                && (int) $receta['usuario_id'] > 0;
        }
    } catch (mysqli_sql_exception $e) {
        error_log('Error consultando receta: ' . $e->getMessage());
        http_response_code(500);
        $error = 'No fue posible consultar la receta.';
    }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Detalle de receta | AmbienteWebCS</title><link rel="stylesheet" href="interior.css"></head><body class="site-app"><div class="app-shell"><header class="site-header"><a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true">🌱</span><span>Ambiente<span class="brand-accent">WebCS</span></span></a><nav class="site-nav" aria-label="Navegación principal"><a href="recetas.html" aria-current="page">Recetas</a><a href="donacion.html">Donaciones</a><a href="mapa.html">Puntos de recolección</a><a href="educacion.html">Aprender</a></nav><div class="site-account"><a id="siteLogin" href="login.html" class="login-action">Iniciar sesión</a><div id="siteSigned" class="site-account" hidden><span id="siteUser" class="user-name"></span><form action="logout.php" method="post"><input type="hidden" id="siteLogoutCsrf" name="csrf_logout"><button type="submit">Cerrar sesión</button></form></div></div></header><main class="page-container"><div class="page-intro"><a class="breadcrumb" href="recetas.html">← Volver a recetas</a><p class="eyebrow">DETALLE DE RECETA</p><h1><?= $receta ? htmlspecialchars((string) $receta['nombre'], ENT_QUOTES, 'UTF-8') : 'Receta no disponible' ?></h1><p class="lede">Aprovecha lo que tienes en casa con ideas de la comunidad.</p></div>
<?php if ($error !== ''): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php else: ?>
<div class="info-columns"><section class="panel"><span class="tile-icon">🥕</span><h2>Ingredientes</h2><p><?= nl2br(htmlspecialchars((string) $receta['ingredientes'], ENT_QUOTES, 'UTF-8')) ?></p></section><section class="panel"><span class="tile-icon">🍳</span><h2>Preparación</h2><p><?= nl2br(htmlspecialchars((string) $receta['instrucciones'], ENT_QUOTES, 'UTF-8')) ?></p></section></div>
<?php endif; ?>
<?php if ($receta && $isOwner): ?>
<div class="panel" style="margin-top:22px">
    <h2>Administrar mi receta</h2>
    <p>Solo tú puedes editar o eliminar esta preparación.</p>
    <div class="actions">
        <a class="button-primary" href="editar_receta.php?id=<?= (int) $receta['id'] ?>">Editar receta →</a>
        <form method="post" action="eliminar_receta.php"
            onsubmit="return confirm('¿Eliminar esta receta de forma permanente?');">
            <input type="hidden" name="id" value="<?= (int) $receta['id'] ?>">
            <input type="hidden" name="csrf_token"
                value="<?= htmlspecialchars(ambienteWriteToken(), ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="button-danger">Eliminar receta</button>
        </form>
    </div>
</div>
<?php elseif ($receta && $receta['usuario_id'] === null): ?>
<div class="info-strip">Receta histórica sin propietario identificado. Se conserva para consulta.</div>
<?php endif; ?>
<?php if ($receta && isset($_GET['updated']) && $_GET['updated'] === '1' && $isOwner): ?>
<div class="alert" role="status">Receta actualizada correctamente.</div>
<?php endif; ?>
<div class="actions"><a href="buscarRecetas.html" class="button-primary">Buscar otra receta →</a><a href="recetas.html" class="button-secondary">Menú de recetas</a></div></main><footer class="site-footer"><span>© AmbienteWebCS · Proyecto educativo</span><span>Demostración de reducción de desperdicio alimentario</span></footer></div><script src="site.js" defer></script></body></html>
