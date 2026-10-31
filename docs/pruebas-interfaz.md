# Pruebas manuales — AmbienteWebCS

Ejecutar sobre una **base MySQL de pruebas**. Para un clon nuevo ver [README](../README.md).
Las ubicaciones/donaciones son de demostración.

## Recorrido actual

1. Página principal `Avance2/index.html`: revisar navegación y diseño móvil.
2. `login.html`: probar acceso correcto e incorrecto. Verificar el mensaje genérico y el botón mostrar/ocultar contraseña.
3. `registro.html`: crear una cuenta de prueba y rechazar contraseñas no coincidentes.
4. `session_status.php`: tras login informa sesión autenticada; tras logout vuelve a no autenticada.
5. `recetas.html` y `recetas_populares.php`: explorar recetas recientes.
6. `buscarRecetas.html` y `categorias.html`: filtrar por ingredientes.
7. `nuevaReceta.html`: un visitante no puede publicar; el usuario autenticado sí.
8. Abrir una receta creada: su propietario puede editar y eliminar; otro usuario no.
9. `donacion.html`: visitantes bloqueados; usuarios registrados pueden guardar donaciones ficticias.
10. `mapa.html`: las ubicaciones son ilustrativas, **no** puntos de donación verificados.
11. `educacion.html`: comprobar tarjetas, recomendaciones y navegación.
12. `recuperar_contrasena.html`: solicitar un enlace para un correo de prueba registrado. Confirmar entrega SMTP, caducidad y uso único.
13. Comprobar en escritorio y móvil sin pérdida de controles de navegación.

## Seguridad

- Los formularios de escritura deben rechazar un POST sin token CSRF.
- Las acciones de modificación deben exigir sesión válida.
- La eliminación de recetas es POST, no GET, y requiere propiedad de la receta.
- El login, registro y recuperación limitan intentos por IP durante ventanas cortas.
- Nunca usar cuentas reales o datos personales en capturas de portafolio.

## Advertencia

La ejecución local correcta no equivale a seguridad de producción. Para
desplegar a Internet se requiere revisión de hosting, HTTPS, antispam,
protección de secretos y pruebas de penetración pertinentes.
