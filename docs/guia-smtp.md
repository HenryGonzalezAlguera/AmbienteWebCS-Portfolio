# Recuperación de contraseña por SMTP

AmbienteWebCS utiliza **PHPMailer** con autenticación SMTP y TLS.
El correo saliente es opcional para explorar recetas; se necesita para
restablecer contraseñas mediante email.

## 1. Requisitos

- PHP 8.1+ y extensiones `openssl` y `mysqli`.
- Composer y dependencia PHPMailer instalada con `composer install`.
- Un proveedor SMTP autenticado (Gmail o servicio de correo transaccional).
- Base de datos instalada desde `database/01_schema.sql` o migrada.

## 2. Configuración privada

Desde la raíz de un clon de AmbienteWebCS:

```powershell
Copy-Item .\Avance2\smtp_config.example.php .\Avance2\smtp_config.local.php
notepad .\Avance2\smtp_config.local.php
```

Completa los parámetros **en el archivo privado**, que está excluido de Git:

| Parámetro | Ejemplo para Gmail |
| --- | --- |
| APP_BASE_URL | `http://localhost/AmbienteWebCS-Portfolio/Avance2` (local) |
| SMTP_HOST | `smtp.gmail.com` |
| SMTP_PORT | `587` |
| SMTP_ENCRYPTION | `tls` |
| SMTP_USER | Tu cuenta Gmail completa |
| SMTP_PASSWORD | Contraseña de aplicación de Gmail |
| MAIL_FROM | Misma dirección configurada en SMTP_USER |
| MAIL_FROM_NAME | `AmbienteWebCS` |

Para enviar correo desde un sitio público configura `APP_BASE_URL` con
**HTTPS** y la ruta correcta de producción. El código no permite
URLs HTTP externas, ni construye enlaces a partir de cabeceras Host.
Gmail requiere autenticación; si las contraseñas de aplicación no están
disponibles en tu cuenta, utiliza un proveedor transaccional compatible
o una implementación OAuth2 debidamente revisada.

## 3. Diagnóstico seguro

```powershell
php scripts/diagnostico_smtp.php
```

Esta comprobación revisa configuración, OpenSSL, DNS y conexión TCP.
No envía mensajes. Si termina correctamente:

```powershell
php scripts/diagnostico_smtp.php --send-test
```

El segundo comando envía **un correo de prueba al remitente configurado**.
Nunca imprime secretos, destinatarios ni el mensaje de error SMTP completo.

- **Configuración incompleta:** revisa archivo local y Composer.
- **Puerto inaccesible:** revisa red, firewall o bloqueo del hosting.
- **Autenticación fallida:** verifica correo de usuario, cuenta, contraseña
  de aplicación y método de autenticación admitido por el proveedor.
- **SMTP aceptó el correo, pero no llegó:** revisa spam y los registros
  del proveedor. Aceptación SMTP no garantiza entrega final.

## 4. Prueba funcional

1. Crea una cuenta **de prueba** mediante `registro.html`.
2. Abre `recuperar_contrasena.html` y solicita el enlace.
3. La pantalla confirma la recepción de la solicitud sin revelar
   si la cuenta realmente existe.
4. Recibe el email, abre el enlace y define una contraseña nueva.
5. Comprueba que el acceso nuevo funciona y el enlace no puede usarse otra vez.

Los enlaces caducan a los **30 minutos** y la base guarda un hash SHA-256.
Mientras exista un enlace vigente para una cuenta, no se reenvían enlaces
nuevos a esa dirección.

## Seguridad y límites

No compartas `smtp_config.local.php`, contraseñas, tokens o capturas
con datos reales. La aplicación limita solicitudes por IP en el servidor
y la recuperación usa consultas parametrizadas. Esto no sustituye la
monitorización de correo, políticas de abuso de proveedores o HTTPS.

Para publicar el **código** sin historial sensible, sigue
[PUBLICACION_SEGURA.md](PUBLICACION_SEGURA.md).
