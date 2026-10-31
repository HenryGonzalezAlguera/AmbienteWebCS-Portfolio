# Seguridad y alcance de la edición de portafolio

Esta edición presenta código fuente de aprendizaje y demostración. **No es un servicio
desplegado ni cuenta con una auditoría de seguridad de producción.**

## Controles incluidos

- Registro e inicio de sesión con contraseñas hasheadas mediante funciones nativas de PHP.
- Sesiones con políticas de cookies `HttpOnly` y `SameSite=Lax`; `Secure` cuando se sirve con HTTPS.
- Protección CSRF en operaciones que escriben datos.
- Autorización por propietario en edición y eliminación de recetas.
- Consultas preparadas MySQLi para parámetros de usuario.
- Recuperación por enlace de un solo uso y expiración de 30 minutos,
  SMTP autenticado mediante PHPMailer y cifrado TLS.
- Limitación básica de peticiones por IP, guardada en archivos temporales del servidor.
- Plantillas de configuración sin contraseñas y exclusión mediante `.gitignore`.
- GitHub Actions para validar sintaxis, integración MySQL/HTTP y archivos controlados.

## Antes de alojar la web en Internet

Utiliza HTTPS, variables de entorno o un gestor de secretos, una cuenta MySQL con privilegios
mínimos, protección frente a abuso distribuido y observabilidad de los envíos.
Evalúa controles adicionales contra registros automatizados y un análisis de dependencias.
El limitador en disco de la demostración no sustituye la protección multiinstancia.

La base y los correos de demostración no deben contener datos personales reales.

## Publicación del código

El repositorio fue construido desde un árbol de archivos revisado, con historial Git
independiente. No contiene archivos de configuración local de MySQL ni Gmail.
No se incluye una licencia de reutilización porque la atribución y autorización
del proyecto académico colaborativo deben confirmarse con los autores originales.
