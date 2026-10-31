<?php
/**
 * Copia este archivo como smtp_config.local.php y configura tus valores.
 * smtp_config.local.php está excluido por .gitignore: NUNCA subir credenciales.
 * Para Gmail, SMTP_PASSWORD es una contraseña de aplicación, no la de tu cuenta.
 */
return [
    'APP_BASE_URL' => 'http://localhost/AmbienteWebCS-Portfolio/Avance2',
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_PORT' => '587',
    'SMTP_ENCRYPTION' => 'tls',
    'SMTP_USER' => 'tu-cuenta@gmail.com',
    'SMTP_PASSWORD' => 'REEMPLAZAR_SOLO_EN_EL_ARCHIVO_LOCAL',
    'MAIL_FROM' => 'tu-cuenta@gmail.com',
    'MAIL_FROM_NAME' => 'AmbienteWebCS',
];
