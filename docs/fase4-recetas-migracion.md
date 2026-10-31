# Gestión de recetas propias y migración de propietarios

La aplicación exige sesión PHP y token CSRF para crear, editar y eliminar recetas.
Cada nueva receta registra `usuario_id`; la propiedad se verifica también en el servidor.

## Bases antiguas de demostración

El esquema `database/01_schema.sql` ya incluye la propiedad de recetas. Solo debes ejecutar
`database/05_migracion_propietarios.sql` cuando estés actualizando una base **anterior**
llamada `reduccion_desperdicio_portafolio` que no tenga `recetas.usuario_id`.

1. Exporta primero un respaldo privado desde phpMyAdmin.
2. Verifica que el script apunte exactamente a tu base de pruebas.
3. Ejecuta la migración desde una cuenta MySQL con privilegios DDL.
4. Confirma que la columna `usuario_id` y su clave foránea se hayan agregado.
5. Las recetas antiguas sin propietario (`usuario_id IS NULL`) permanecen en modo lectura.

## Pruebas de autorización

- Sin iniciar sesión se pueden consultar recetas, pero no publicar ni gestionar.
- Un usuario registrado puede crear recetas, editarlas y eliminarlas desde su detalle.
- Otro usuario no puede alterar una receta ajena, ni mediante peticiones HTTP directas.
- Las solicitudes de escritura sin CSRF o con método indebido deben ser rechazadas.

No uses datos personales reales ni publiques respaldos de la base.
