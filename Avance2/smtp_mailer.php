<?php
declare(strict_types=1);

/**
 * Transporte de correo SMTP: credenciales exclusivamente en entorno o archivo ignorado.
 * Instalación local: composer install (crea vendor/autoload.php).
 */

function ambienteSmtpSettings(): array
{
    $path = __DIR__ . '/smtp_config.local.php';
    $local = is_file($path) ? require $path : [];
    if (!is_array($local)) {
        error_log('SMTP: archivo de configuración local inválido.');
        $local = [];
    }

    $setting = static function (string $key, string $default = '') use ($local): string {
        $environment = getenv($key);
        return $environment !== false ? (string) $environment : (string) ($local[$key] ?? $default);
    };

    $url = rtrim(trim($setting('APP_BASE_URL')), '/');
    $host = trim($setting('SMTP_HOST'));
    $port = filter_var($setting('SMTP_PORT', '587'), FILTER_VALIDATE_INT);
    $encryption = strtolower(trim($setting('SMTP_ENCRYPTION', 'tls')));
    $username = trim($setting('SMTP_USER'));
    $password = $setting('SMTP_PASSWORD');
    $from = trim($setting('MAIL_FROM'));
    $fromName = trim($setting('MAIL_FROM_NAME', 'AmbienteWebCS'));

    // Nunca derivar la URL del reset de Host/X-Forwarded-Host de la solicitud.
    $parts = filter_var($url, FILTER_VALIDATE_URL) ? parse_url($url) : false;
    $scheme = is_array($parts) ? strtolower((string) ($parts['scheme'] ?? '')) : '';
    $urlHost = is_array($parts) ? strtolower((string) ($parts['host'] ?? '')) : '';
    $isLocalHttp = $scheme === 'http' && in_array($urlHost, ['localhost', '127.0.0.1'], true);
    $validUrl = is_array($parts) && ($scheme === 'https' || $isLocalHttp)
        && !isset($parts['user']) && !isset($parts['pass'])
        && !isset($parts['query']) && !isset($parts['fragment']);

    $hasNewline = static fn(string $value): bool => strpbrk($value, "\r\n") !== false;
    $validHost = preg_match('/^[a-zA-Z0-9.-]+$/D', $host) === 1;
    $validPort = is_int($port) && $port > 0 && $port <= 65535;
    $validSecurity = in_array($encryption, ['tls', 'ssl'], true);
    $validFrom = filter_var($from, FILTER_VALIDATE_EMAIL) !== false;

    $ready = $validUrl && $validHost && $validPort && $validSecurity && $validFrom
        && $username !== '' && $password !== ''
        && $password !== 'REEMPLAZAR_SOLO_EN_EL_ARCHIVO_LOCAL'
        && !$hasNewline($username) && !$hasNewline($fromName)
        && is_file(dirname(__DIR__) . '/vendor/autoload.php');

    return [
        'ready' => $ready,
        'base_url' => $url,
        'host' => $host,
        'port' => $port,
        'encryption' => $encryption,
        'username' => $username,
        'password' => $password,
        'from' => $from,
        'from_name' => $fromName,
    ];
}

function ambienteSendRecoverySmtp(array $config, string $recipient, string $url): bool
{
    if (($config['ready'] ?? false) !== true || !filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    try {
        require_once dirname(__DIR__) . '/vendor/autoload.php';

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) $config['host'];
        $mail->Port = (int) $config['port'];
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
        $mail->addAddress($recipient);
        $mail->isHTML(false);
        $mail->Subject = 'Recuperación de contraseña - AmbienteWebCS';
        $mail->Body = "Solicitaste restablecer tu contraseña. Abre este enlace, válido por 30 minutos:\n\n"
            . $url . "\n\nSi no realizaste esta solicitud, puedes ignorar el mensaje.\n";
        $mail->send();
        return true;
    } catch (Throwable $error) {
        // No registrar credenciales, token, dirección del destinatario ni respuesta SMTP.
        error_log('SMTP: no fue posible entregar la solicitud de recuperación.');
        return false;
    }
}
