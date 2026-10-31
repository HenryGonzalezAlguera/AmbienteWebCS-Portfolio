<?php
declare(strict_types=1);

// Nunca cachear respuestas de recuperación ni filtrar datos del usuario.
header('Cache-Control: no-store, private');
header('Referrer-Policy: no-referrer');
header('X-Content-Type-Options: nosniff');

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: recuperar_contrasena.html', true, 303);
    exit;
}

require_once __DIR__ . '/security_rate_limit.php';
if (!ambienteAllowRequest('password_reset', 8, 900)) {
    header('Retry-After: 900');
    http_response_code(429);
    exit('Se alcanzó el límite de solicitudes. Vuelve a intentarlo más tarde.');
}

$email = strtolower(trim((string) ($_POST['email'] ?? '')));
require_once __DIR__ . '/smtp_mailer.php';
$smtp = ambienteSmtpSettings();
$baseUrl = (string) $smtp['base_url'];
$genericMessage = 'Si existe una cuenta asociada, recibirás instrucciones para recuperar tu contraseña.';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    http_response_code(422);
    exit('Ingresa un correo electrónico válido.');
}

// La configuración local también valida TLS y la existencia de PHPMailer.
$mailReady = $smtp['ready'] === true;

require_once __DIR__ . '/db_config.php';
$tokenToSend = null;
try {
    // Serializar solicitudes al mismo correo en la fila de su usuario.
    $conn->begin_transaction();
    $stmt = $conn->prepare('SELECT email FROM usuarios WHERE email = ? LIMIT 1 FOR UPDATE');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $exists = $stmt->get_result()->fetch_assoc() !== null;
    $stmt->close();

    if ($exists && $mailReady) {
        $now = time();
        // Un enlace vigente evita enviar múltiples mensajes en los próximos 30 minutos.
        $stmt = $conn->prepare(
            'SELECT id FROM recuperacion_contrasena WHERE email = ? AND expires > ? LIMIT 1'
        );
        $stmt->bind_param('si', $email, $now);
        $stmt->execute();
        $pending = $stmt->get_result()->fetch_assoc() !== null;
        $stmt->close();

        if (!$pending) {
            // Solo limpiamos solicitudes antiguas del mismo correo, nunca otros usuarios.
            $stmt = $conn->prepare('DELETE FROM recuperacion_contrasena WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->close();

            $tokenToSend = bin2hex(random_bytes(32));
            $hash = hash('sha256', $tokenToSend);
            $expires = $now + 1800;
            $stmt = $conn->prepare(
                'INSERT INTO recuperacion_contrasena (email, token_hash, expires) VALUES (?, ?, ?)'
            );
            $stmt->bind_param('ssi', $email, $hash, $expires);
            $stmt->execute();
            $stmt->close();
        }
    } elseif ($exists && !$mailReady) {
        error_log('Recuperación no configurada: falta SMTP, URL válida o dependencia Composer.');
    }
    $conn->commit();
} catch (Throwable $e) {
    try {
        $conn->rollback();
    } catch (Throwable $ignored) {
        // El error original sigue registrado.
    }
    $tokenToSend = null;
    error_log('Error al solicitar recuperación: ' . $e->getMessage());
}

if ($tokenToSend !== null) {
    $link = $baseUrl . '/recuperar_contrasena_form.php?token=' . rawurlencode($tokenToSend);
    // Transporte SMTP cifrado: ninguna credencial se publica ni se entrega al navegador.
    $sent = ambienteSendRecoverySmtp($smtp, $email, $link);
    if (!$sent) {
        error_log('SMTP no pudo enviar el correo de recuperación.');
        try {
            $tokenHash = hash('sha256', $tokenToSend);
            $stmt = $conn->prepare('DELETE FROM recuperacion_contrasena WHERE token_hash = ?');
            $stmt->bind_param('s', $tokenHash);
            $stmt->execute();
            $stmt->close();
        } catch (Throwable $e) {
            error_log('No se pudo limpiar token de correo fallido: ' . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Recuperación de cuenta | AmbienteWebCS</title><link rel="stylesheet" href="interior.css"></head>
<body class="site-app"><div class="app-shell">
<header class="site-header"><a class="brand" href="index.html"><span class="brand-mark">🌱</span>Ambiente<span class="brand-accent">WebCS</span></a><nav class="site-nav"><a href="index.html">Inicio</a><a href="login.html">Iniciar sesión</a></nav></header>
<main class="page-container page-narrow">
<a class="breadcrumb" href="login.html">← Volver al login</a>
<div class="page-intro"><p class="eyebrow">SEGURIDAD DE TU CUENTA</p><h1>Solicitud recibida</h1></div>
<div class="panel"><div class="alert" role="status"><?= htmlspecialchars($genericMessage, ENT_QUOTES, 'UTF-8') ?></div>
<p>Si tu cuenta existe y el correo saliente está configurado, revisa tu bandeja de entrada y la carpeta de correo no deseado.</p>
<div class="actions"><a class="button-primary" href="login.html">Volver al inicio de sesión →</a></div>
</div></main><footer class="site-footer">© AmbienteWebCS · Proyecto educativo</footer>
</div></body></html>
