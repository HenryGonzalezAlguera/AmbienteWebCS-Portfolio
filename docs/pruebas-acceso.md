# Pruebas del área de acceso

Esta guía aplica a las pantallas actuales `login.html`, `registro.html`
y `recuperar_contrasena.html`.

- Abrir desde `http://localhost/AmbienteWebCS/Avance2/login.html`.
- Validar inicio de sesión correcto: redirección a `index.html`.
- Credenciales incorrectas: mensaje visible, sin detalles de la cuenta.
- Registro: validar correo, nombre y contraseña; mensaje al duplicar correo.
- Tras cerrar sesión: mensaje de confirmación y acciones privadas bloqueadas.
- Botón mostrar/ocultar: no altera el valor ni envía la contraseña a otro destino.
- Recuperación: envío real a cuenta de prueba si SMTP está configurado y
  enlace temporal de un solo uso; en otras instalaciones, revisar configuración.
- Adaptación: comprobar los tres formularios en tamaños móvil y escritorio.
- Cookies: `HttpOnly`, `SameSite=Lax`; `Secure` al servir el sitio por HTTPS.

No compartir configuraciones locales, enlaces con tokens, contraseñas ni
capturas que permitan identificar usuarios reales.

Los formularios HTML siguen apuntando a los endpoints PHP existentes;
los estilos no implementan autenticación por sí mismos.
