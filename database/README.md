# Base de datos — AmbienteWebCS

Esquema reconstruido para ejecutar localmente la aplicación PHP (no se encontró un respaldo SQL original).

## Instalación

1. Inicia MySQL en XAMPP o un servidor compatible.
2. Abre phpMyAdmin y entra a **SQL**.
3. Ejecuta `01_schema.sql`.
4. Opcional: ejecuta `02_recetas_demo.sql` una sola vez para cargar tres recetas ficticias.
5. Copia `Avance2/db_config.example.php` como `Avance2/db_config.local.php` y configura tus credenciales MySQL locales. Este archivo local está excluido en `.gitignore`.
6. Crea usuarios desde la pantalla de registro; no existen cuentas ni contraseñas predeterminadas.

## Tablas

| Tabla | Uso |
| --- | --- |
| `usuarios` | Registro e inicio de sesión con hashes PHP |
| `recetas` | Creación, búsqueda y consulta de recetas |
| `donaciones` | Registro de donaciones |
| `recuperacion_contrasena` | Tokens de recuperación almacenados como hashes SHA-256 y expiración |

**Importante:** si ya existía una base `reduccion_desperdicio`, realiza una copia de seguridad antes de ejecutar el esquema. `CREATE TABLE IF NOT EXISTS` no migra tablas preexistentes ni valida sus columnas. Este esquema corresponde al código de la rama de recuperación, no es un respaldo de datos históricos.
