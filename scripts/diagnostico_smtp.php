<?php
declare(strict_types=1);

/**
 * Diagnóstico local de SMTP para AmbienteWebCS.
 * Ejecutar: php scripts/diagnostico_smtp.php
 * Envío real opcional y EXPLÍCITO: php scripts/diagnostico_smtp.php --send-test
 * Exclusivo para CLI. No revela direcciones, claves ni respuestas detalladas del servidor.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Disponible únicamente desde una consola local.');
}

require_once dirname(__DIR__) . '/Avance2/smtp_mailer.php';

$config = ambienteSmtpSettings();
$sendTest = in_array('--send-test', $argv, true);
echo "AmbienteWebCS | Diagnóstico SMTP (sin imprimir secretos)\n";

if (!extension_loaded('openssl')) {
    echo "[ERROR] PHP no tiene habilitada la extensión openssl.\n";
    exit(1);
}
echo "[OK] Extensión OpenSSL disponible.\n";

if (!is_file(dirname(__DIR__) . '/vendor/autoload.php')) {
    echo "[ERROR] Falta vendor/autoload.php. Ejecuta composer install.\n";
    exit(1);
}
echo "[OK] Dependencias instaladas.\n";

if (!$config['ready']) {
    echo "[ERROR] Configuración SMTP incompleta. Revisa smtp_config.local.php.\n";
    exit(1);
}
echo "[OK] Configuración SMTP cargada.\n";

if (strcasecmp((string) $config['username'], (string) $config['from']) !== 0) {
    echo "[AVISO] Con Gmail conviene usar el mismo correo en SMTP_USER y MAIL_FROM.\n";
}

$host = (string) $config['host'];
$port = (int) $config['port'];
$resolved = gethostbyname($host);
if ($resolved === $host && filter_var($host, FILTER_VALIDATE_IP) === false) {
    echo "[ERROR] No fue posible resolver el nombre del servidor SMTP.\n";
    exit(2);
}
echo "[OK] Resolución DNS disponible.\n";

$errno = 0;
$errstr = '';
$connection = @stream_socket_client(
    'tcp://' . $host . ':' . $port,
    $errno,
    $errstr,
    8,
    STREAM_CLIENT_CONNECT
);
if ($connection === false) {
    echo "[ERROR] No se pudo establecer la conexión TCP al puerto SMTP.\n";
    echo "Comprueba Internet, el puerto y posibles restricciones del antivirus o firewall.\n";
    exit(2);
}
fclose($connection);
echo "[OK] Puerto SMTP accesible.\n";

if (!$sendTest) {
    echo "Diagnóstico básico completo. Para probar autenticación y envío a tu propio remitente:\n";
    echo "php scripts/diagnostico_smtp.php --send-test\n";
    exit(0);
}

echo "Probando autenticación y envío de un único correo a MAIL_FROM...\n";

require_once dirname(__DIR__) . '/vendor/autoload.php';
$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = $host;
    $mail->Port = $port;
    $mail->SMTPAuth = true;
    $mail->Username = (string) $config['username'];
    $mail->Password = (string) $config['password'];
    $mail->SMTPSecure = $config['encryption'] === 'ssl'
        ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->SMTPDebug = 0;
    $mail->Timeout = 15;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom((string) $config['from'], (string) $config['from_name']);
    $mail->addAddress((string) $config['from']);
    $mail->isHTML(false);
    $mail->Subject = 'AmbienteWebCS - Prueba de configuracion SMTP';
    $mail->Body = 'Este es un mensaje de prueba de SMTP. No contiene enlaces ni tokens de recuperación.';
    $mail->send();
    echo "[OK] Gmail/SMTP aceptó el mensaje de prueba. Revisa tu bandeja y correo no deseado.\n";
    echo "Nota: aceptación SMTP no garantiza entrega final al buzón.\n";
} catch (Throwable $error) {
    $details = strtolower((string) $mail->ErrorInfo . ' ' . $error->getMessage());
    if (str_contains($details, 'authenticat') || str_contains($details, 'credential')
        || str_contains($details, 'username and password')) {
        echo "[ERROR] Falló la autenticación SMTP. Revisa SMTP_USER y la contraseña de aplicación.\n";
    } elseif (str_contains($details, 'connect') || str_contains($details, 'timed out')
        || str_contains($details, 'certificate') || str_contains($details, 'tls')) {
        echo "[ERROR] Falló la negociación de conexión o TLS. Comprueba OpenSSL y certificados.\n";
    } else {
        echo "[ERROR] El servidor no aceptó el envío. Revisa la configuración local y restricciones de Gmail.\n";
    }
    // No imprimir ErrorInfo: puede contener datos del servidor o de la cuenta.
    exit(3);
}
