<?php
declare(strict_types=1);
$recetas = [];
$error = '';
require_once __DIR__ . '/db_config.php';
try {
    $result = $conn->query('SELECT id, nombre, ingredientes FROM recetas ORDER BY id DESC LIMIT 24');
    $recetas = $result->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $e) {
    error_log('Error al consultar recetas recientes: ' . $e->getMessage());
    http_response_code(500);
    $error = 'No fue posible consultar las recetas.';
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recetas recientes | AmbienteWebCS</title><link rel="stylesheet" href="interior.css"></head><body class="site-app"><div class="app-shell"><header class="site-header"><a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true">🌱</span><span>Ambiente<span class="brand-accent">WebCS</span></span></a><nav class="site-nav" aria-label="Navegación principal"><a href="recetas.html" aria-current="page">Recetas</a><a href="donacion.html">Donaciones</a><a href="mapa.html">Puntos de recolección</a><a href="educacion.html">Aprender</a></nav><div class="site-account"><a id="siteLogin" href="login.html" class="login-action">Iniciar sesión</a><div id="siteSigned" class="site-account" hidden><span id="siteUser" class="user-name"></span><form action="logout.php" method="post"><input type="hidden" id="siteLogoutCsrf" name="csrf_logout"><button type="submit">Cerrar sesión</button></form></div></div></header><main class="page-container"><div class="page-intro"><a class="breadcrumb" href="recetas.html">← Volver a recetas</a><p class="eyebrow">IDEAS DE LA COMUNIDAD</p><h1>Recetas recientes</h1><p class="lede">Explora las últimas recetas registradas para aprovechar ingredientes.</p></div><?php if ($error !== ''): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php elseif (count($recetas) === 0): ?><div class="panel"><p>Todavía no hay recetas disponibles. ¡Puedes agregar la primera!</p></div><?php else: ?><div class="grid"><?php foreach ($recetas as $receta): ?><a class="feature-link" href="recetas.php?id=<?= (int) $receta['id'] ?>"><span class="tile-icon" aria-hidden="true">🍲</span><h2><?= htmlspecialchars((string) $receta['nombre'], ENT_QUOTES, 'UTF-8') ?></h2><p><?= htmlspecialchars((string) $receta['ingredientes'], ENT_QUOTES, 'UTF-8') ?></p><strong>Ver preparación →</strong></a><?php endforeach; ?></div><?php endif; ?><div class="actions"><a class="button-primary" href="nuevaReceta.html">Agregar receta →</a><a class="button-secondary" href="buscarRecetas.html">Buscar por ingrediente</a></div></main><footer class="site-footer"><span>© AmbienteWebCS · Proyecto educativo</span><span>Demostración de reducción de desperdicio alimentario</span></footer></div><script src="site.js" defer></script></body></html>
