<?php
declare(strict_types=1);
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');
require_once __DIR__ . '/csrf.php';
ambienteStartSession();

$token = (string) ($_POST['token'] ?? $_GET['token'] ?? '');
$tokenValido = strlen($token) === 64 && ctype_xdigit($token);
$isPost = ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
$mensaje = '';
$mostrarFormulario = false;

if ($tokenValido) {
    require_once __DIR__ . '/db_config.php';
    try {
        $hash = hash('sha256', $token);
        $now = time();
        // El cambio de contraseña y el consumo del token se realizan bajo el mismo bloqueo.
        if ($isPost) {
            $conn->begin_transaction();
        }
        $sql = 'SELECT email FROM recuperacion_contrasena WHERE token_hash = ? AND expires > ? LIMIT 1'
            . ($isPost ? ' FOR UPDATE' : '');
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $hash, $now);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            if ($isPost) {
                $password = (string) ($_POST['nueva_contrasena'] ?? '');
                $confirmation = (string) ($_POST['confirmar_contrasena'] ?? '');
                $csrf = (string) ($_POST['csrf_token'] ?? '');

                if (!isset($_SESSION['csrf_reset']) || !hash_equals((string) $_SESSION['csrf_reset'], $csrf)) {
                    http_response_code(403);
                    $mensaje = 'La sesión del formulario expiró. Abre nuevamente el enlace.';
                } elseif (strlen($password) < 8 || strlen($password) > 72 || $password !== $confirmation) {
                    http_response_code(422);
                    $mensaje = 'Las contraseñas deben coincidir y tener entre 8 y 72 caracteres.';
                    $mostrarFormulario = true;
                } else {
                    try {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $email = (string) $row['email'];
                        $stmt = $conn->prepare('UPDATE usuarios SET password = ? WHERE email = ?');
                        $stmt->bind_param('ss', $newHash, $email);
                        $stmt->execute();
                        $changed = $stmt->affected_rows;
                        $stmt->close();
                        if ($changed !== 1) {
                            throw new RuntimeException('No se encontró la cuenta para restablecer.');
                        }
                        $stmt = $conn->prepare('DELETE FROM recuperacion_contrasena WHERE email = ?');
                        $stmt->bind_param('s', $email);
                        $stmt->execute();
                        $stmt->close();
                        $conn->commit();
                        unset($_SESSION['csrf_reset']);
                        $mensaje = 'Contraseña actualizada correctamente.';
                    } catch (Throwable $e) {
                        $conn->rollback();
                        throw $e;
                    }
                }
                // Los POST no válidos no pueden dejar transacciones abiertas.
                if ($mensaje !== '' && $mensaje !== 'Contraseña actualizada correctamente.') {
                    $conn->rollback();
                }
            } else {
                $_SESSION['csrf_reset'] = bin2hex(random_bytes(32));
                $mostrarFormulario = true;
            }
        } else {
            if ($isPost) {
                $conn->rollback();
            }
            $mensaje = 'El enlace ha caducado o ya se utilizó.';
        }
    } catch (Throwable $e) {
        error_log('Error al restablecer contraseña: ' . $e->getMessage());
        http_response_code(500);
        $mensaje = 'No se pudo completar la operación.';
    }
} else {
    $mensaje = 'El enlace no es válido.';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer contraseña | AmbienteWebCS</title>
    <link rel="stylesheet" href="interior.css">
</head>
<body class="site-app"><div class="app-shell"><header class="site-header"><a class="brand" href="index.html"><span class="brand-mark" aria-hidden="true">🌱</span><span>Ambiente<span class="brand-accent">WebCS</span></span></a><nav class="site-nav" aria-label="Navegación principal"><a href="recetas.html">Recetas</a><a href="donacion.html">Donaciones</a><a href="mapa.html">Puntos de recolección</a><a href="educacion.html">Aprender</a></nav><div class="site-account"><a id="siteLogin" href="login.html" class="login-action">Iniciar sesión</a><div id="siteSigned" class="site-account" hidden><span id="siteUser" class="user-name"></span><form action="logout.php" method="post"><input type="hidden" id="siteLogoutCsrf" name="csrf_logout"><button type="submit">Cerrar sesión</button></form></div></div></header>
    <main class="page-container page-narrow"><a class="breadcrumb" href="login.html">← Volver al login</a><div class="page-intro"><p class="eyebrow">SEGURIDAD DE TU CUENTA</p><h1>Restablecer contraseña</h1><p class="lede">Define una nueva contraseña para tu cuenta.</p></div><div class="panel">
        <?php if ($mensaje !== ''): ?>
            <p class="alert" role="status"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if ($mostrarFormulario): ?>
            <form class="ui-form" method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string) $_SESSION['csrf_reset'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="nueva_contrasena">Nueva contraseña</label>
                <input id="nueva_contrasena" name="nueva_contrasena" type="password" minlength="8" maxlength="72" required autocomplete="new-password">
                <label for="confirmar_contrasena">Confirmar contraseña</label>
                <input id="confirmar_contrasena" name="confirmar_contrasena" type="password" minlength="8" maxlength="72" required autocomplete="new-password">
                <button class="primary" type="submit">Actualizar contraseña</button>
            </form>
        <?php endif; ?>
        <p><a href="login.html">Volver al inicio de sesión</a></p></div>
    </main><footer class="site-footer"><span>© AmbienteWebCS · Proyecto educativo</span></footer></div><script src="site.js" defer></script>
</body>
</html>
