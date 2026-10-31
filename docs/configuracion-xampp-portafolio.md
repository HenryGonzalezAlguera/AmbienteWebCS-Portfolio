# Configuración de MySQL en XAMPP

La aplicación admite MySQL 8 o MariaDB. Se recomienda una base de datos de demostración aislada.

## Instalación nueva

1. Inicia Apache y MySQL desde XAMPP y verifica el puerto desde phpMyAdmin con `SELECT @@port;`.
2. Para una instalación nueva importa `database/01_schema.sql`. Opcionalmente ejecuta una sola vez `database/02_recetas_demo.sql`.
3. Copia `Avance2/db_config.example.php` como `Avance2/db_config.local.php`.
4. Completa los parámetros de MySQL en tu equipo (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`). Usa un usuario con permisos mínimos.
5. Abre `http://localhost/AmbienteWebCS-Portfolio/Avance2/index.html` y crea una cuenta de prueba desde Registro.

## Una base existente

Si ya usas `reduccion_desperdicio_portafolio`, haz primero una copia de seguridad y revisa
`database/05_migracion_propietarios.sql` antes de aplicar cambios. **No repitas importaciones
iniciales en bases con datos**: `CREATE TABLE IF NOT EXISTS` no migra estructuras previas.

## Protección

El archivo `db_config.local.php` y las contraseñas deben permanecer fuera de GitHub.
En entornos públicos exige HTTPS y credenciales MySQL individuales, con permisos limitados.
