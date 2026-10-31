<?php
declare(strict_types=1);
$ingrediente = trim((string) ($_GET['categoria'] ?? ''));
$recetas = [];
$error = '';
if ($ingrediente === '' || strlen($ingrediente) > 180) {
    http_response_code(400);
    $error = 'Selecciona un ingrediente válido.';
} else {
    require_once __DIR__ . '/db_config.php';
    try {
        $search = '%' . $ingrediente . '%';
        $stmt = $conn->prepare('SELECT id, nombre, ingredientes FROM recetas WHERE ingredientes LIKE ? ORDER BY id DESC LIMIT 50');
        $stmt->bind_param('s', $search);
        $stmt->execute();
        $recetas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        error_log('Error al filtrar recetas: ' . $e->getMessage());
        http_response_code(500);
        $error = 'No fue posible completar la búsqueda.';
    }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recetas por ingrediente | AmbienteWebCS</title><link rel="stylesheet" href="interior.css"></head><body class="site-app"><div class="app-shell"><header class="site-header"><a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true">🌱</span><span>Ambiente<span class="brand-accent">WebCS</span></span></a><nav class="site-nav" aria-label="Navegación principal"><a href="recetas.html" aria-current="page">Recetas</a><a href="donacion.html">Donaciones</a><a href="mapa.html">Puntos de recolección</a><a href="educacion.html">Aprender</a></nav><div class="site-account"><a id="siteLogin" href="login.html" class="login-action">Iniciar sesión</a><div id="siteSigned" class="site-account" hidden><span id="siteUser" class="user-name"></span><form action="logout.php" method="post"><input type="hidden" id="siteLogoutCsrf" name="csrf_logout"><button type="submit">Cerrar sesión</button></form></div></div></header><main class="page-container"><div class="page-intro"><a class="breadcrumb" href="categorias.html">← Volver a ingredientes</a><p class="eyebrow">RECETAS POR INGREDIENTE</p><h1><?= $ingrediente !== '' ? 'Recetas con ' . htmlspecialchars($ingrediente, ENT_QUOTES, 'UTF-8') : 'Explorar recetas' ?></h1><p class="lede">Resultados que contienen el ingrediente seleccionado.</p></div><?php if ($error !== ''): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php elseif (!$recetas): ?><div class="panel"><p>No encontramos recetas con ese ingrediente. Prueba otra búsqueda.</p></div><?php else: ?><div class="grid"><?php foreach ($recetas as $receta): ?><a class="feature-link" href="recetas.php?id=<?= (int) $receta['id'] ?>"><span class="tile-icon">🥗</span><h2><?= htmlspecialchars((string) $receta['nombre'], ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars((string) $receta['ingredientes'], ENT_QUOTES, 'UTF-8') ?></p><strong>Ver receta →</strong></a><?php endforeach; ?></div><?php endif; ?><div class="actions"><a href="buscarRecetas.html" class="button-primary">Buscar otro ingrediente →</a></div></main><footer class="site-footer"><span>© AmbienteWebCS · Proyecto educativo</span><span>Demostración de reducción de desperdicio alimentario</span></footer></div><script src="site.js" defer></script></body></html>
