<?php
declare(strict_types=1);

// Prueba sin envío de correos. Ejecutar únicamente en CI con valores ficticios.
if (PHP_SAPI !== 'cli' || getenv('CI_SMOKE') !== '1') {
    exit(2);
}

require_once __DIR__ . '/../Avance2/smtp_mailer.php';

$settings = ambienteSmtpSettings();
if ($settings['ready'] !== true || $settings['encryption'] !== 'tls' || $settings['port'] !== 587) {
    throw new RuntimeException('Configuración SMTP ficticia no reconocida.');
}
putenv('APP_BASE_URL=http://example.org/inseguro');
if (ambienteSmtpSettings()['ready'] !== false) {
    throw new RuntimeException('Una URL HTTP remota no puede enviar enlaces de recuperación.');
}
putenv('APP_BASE_URL=http://localhost/AmbienteWebCS-SMTP/Avance2');
putenv('SMTP_ENCRYPTION=none');
if (ambienteSmtpSettings()['ready'] !== false) {
    throw new RuntimeException('No se debe permitir SMTP sin cifrado.');
}
echo "Validación segura de SMTP: OK (sin correos enviados).\n";
