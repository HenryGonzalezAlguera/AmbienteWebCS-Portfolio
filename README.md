# 🌱 AmbienteWebCS

### Plataforma web educativa para reducir el desperdicio de alimentos

**AmbienteWebCS** es una aplicación web desarrollada con **PHP, MySQL, HTML, CSS y JavaScript** que permite explorar recetas según los ingredientes disponibles, compartir preparaciones, registrar donaciones de demostración y consultar recomendaciones de consumo responsable.

El proyecto nació como trabajo académico y fue sometido a una recuperación técnica y visual: reorganización de interfaces, persistencia MySQL, autenticación, gestión de recetas por propietario, protección de formularios y recuperación de contraseña mediante SMTP.

> **Estado:** versión de portafolio validada localmente con XAMPP y mediante pruebas automatizadas de integración. **No es un servicio desplegado ni auditado para producción.**
>
> Las donaciones y ubicaciones son **demostrativas**: esta aplicación no verifica centros receptores ni coordina entregas reales.
>

---

## Funcionalidades

| Módulo | Qué permite |
| --- | --- |
| **Autenticación** | Registrar usuarios, iniciar y cerrar sesión |
| **Recetas** | Consultar preparaciones, buscar por ingrediente y explorar recetas recientes |
| **Publicación** | Añadir recetas con nombre, ingredientes e instrucciones iniciando sesión |
| **Gestión de recetas** | Editar y eliminar **únicamente las recetas propias** |
| **Donaciones** | Registrar donaciones ficticias con sesión iniciada |
| **Educación ambiental** | Consultar recomendaciones para comprar, conservar y aprovechar alimentos |
| **Ubicaciones de referencia** | Visualizar ubicaciones ilustrativas mediante Google Maps |
| **Recuperación de contraseña** | Solicitar un enlace temporal por correo utilizando PHPMailer y SMTP cifrado |

### Interfaz

- Diseño responsive en tonos verdes inspirado en el aprovechamiento de recursos.
- Navegación coherente entre portada, recetas, donaciones, educación y área de acceso.
- Formularios accesibles con validación en cliente y servidor.
- Páginas de registro, login y recuperación con un diseño de dos columnas.

## Tecnologías

| Área | Tecnología |
| --- | --- |
| Backend | PHP 8.1+ |
| Datos | MySQL 8 / MariaDB, consultas preparadas con **MySQLi** |
| Frontend | HTML5, CSS3, JavaScript |
| Autenticación | Sesiones PHP; `password_hash()` y `password_verify()` |
| Correo | PHPMailer, SMTP autenticado, STARTTLS/SMTPS |
| Dependencias | Composer |
| Entorno local | XAMPP (Apache + PHP) |
| Automatización | GitHub Actions: lint PHP/JS y pruebas HTTP + MySQL |

## Estructura

```text
AmbienteWebCS/
├── Avance2/                      # Páginas, controladores PHP, CSS y JavaScript
│   ├── index.html                # Página principal
│   ├── login.html                # Inicio de sesión
│   ├── registro.html             # Registro
│   ├── recetas.html              # Menú de recetas
│   ├── donacion.html             # Formulario de donaciones
│   ├── db_config.example.php     # Plantilla de MySQL (sin secretos)
│   └── smtp_config.example.php   # Plantilla de SMTP (sin secretos)
├── database/                     # Esquemas, datos ficticios y migraciones
├── docs/                         # Instrucciones y comprobaciones
├── scripts/                      # Pruebas de integración y diagnóstico
├── .github/workflows/            # CI
└── composer.json                 # PHPMailer
```

## Instalación local — Windows y XAMPP

**Requisitos:** XAMPP con PHP 8.1+ y extensión `mysqli`, MySQL 8 o MariaDB, Composer y extensión OpenSSL habilitada. Se recomienda habilitar `zip` para instalar dependencias.

**1. Obtener el código**

```powershell
cd C:\xampp\htdocs
git clone https://github.com/HenryGonzalezAlguera/AmbienteWebCS-Portfolio.git
cd AmbienteWebCS-Portfolio
composer install
```

Si el repositorio continúa privado, necesitas acceso autorizado a GitHub para clonarlo.

**2. Crear una base de datos nueva**

En phpMyAdmin, importa `database/01_schema.sql`: crea la base `reduccion_desperdicio` y las tablas necesarias para la versión actual. Opcionalmente, importa **una sola vez** `database/02_recetas_demo.sql` para disponer de recetas ficticias.

Si ya dispones de la base `reduccion_desperdicio_portafolio`, **no vuelvas a importar el esquema inicial**. Respáldala primero y consulta [la migración incremental de propietarios](docs/fase4-recetas-migracion.md).

**3. Configurar MySQL sin publicar contraseñas**

```powershell
Copy-Item .\Avance2\db_config.example.php .\Avance2\db_config.local.php
notepad .\Avance2\db_config.local.php
```

Completa `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASSWORD` con los valores de tu servidor MySQL. Utiliza una cuenta con privilegios mínimos en la base de pruebas. El archivo `db_config.local.php` está excluido de Git mediante `.gitignore`.

**4. Ejecutar la aplicación**

Inicia Apache y el servicio MySQL correspondiente y abre:

`http://localhost/AmbienteWebCS-Portfolio/Avance2/index.html`

Crea un usuario de prueba en **Registro** para publicar recetas o registrar donaciones. Los visitantes pueden consultar recetas sin iniciar sesión.

## Recuperación de contraseña por SMTP

Para habilitar el envío de mensajes, crea una configuración local:

```powershell
Copy-Item .\Avance2\smtp_config.example.php .\Avance2\smtp_config.local.php
notepad .\Avance2\smtp_config.local.php
```

Ajusta `APP_BASE_URL`, `SMTP_HOST`, `SMTP_PORT`, `SMTP_ENCRYPTION`, `SMTP_USER`, `SMTP_PASSWORD` y `MAIL_FROM` para tu proveedor. Para Gmail puede utilizarse `smtp.gmail.com`, puerto `587`, `tls` y una **contraseña de aplicación**, no la contraseña habitual.

El enlace de recuperación caduca después de 30 minutos y no puede reutilizarse. El sistema almacena el **hash SHA-256** del token, no el enlace en texto plano.

Para comprobar la configuración sin revelar credenciales:

```powershell
php scripts/diagnostico_smtp.php
```

Para probar el envío de **un correo de prueba** al remitente configurado:

```powershell
php scripts/diagnostico_smtp.php --send-test
```

Consulta la [guía de SMTP](docs/guia-smtp.md) para el procedimiento completo.

## Seguridad aplicada

- Contraseñas almacenadas mediante `password_hash()` y validadas con `password_verify()`.
- Consultas SQL preparadas y parámetros tipados para reducir el riesgo de inyección SQL.
- Autenticación de servidor para publicar recetas y registrar donaciones.
- Operaciones de edición y eliminación limitadas al **propietario** de la receta.
- Tokens CSRF para formularios de modificación.
- Tokens temporales para restablecer contraseñas y respuestas que no revelan si una cuenta existe.
- Credenciales de MySQL y SMTP en archivos locales ignorados por Git.
- Cookies PHP con modo estricto, HttpOnly y SameSite=Lax (Secure en HTTPS).
- Límite básico de solicitudes por dirección IP para login, registro y recuperación.
- Protección adicional de recursos internos mediante reglas Apache `.htaccess`.
- Tokens de recuperación consumidos dentro de una transacción con bloqueo de fila.

**Limitaciones:** el límite por IP usa archivos temporales de un solo servidor; no sustituye un WAF, Redis compartido ni protección distribuida contra abuso. Para desplegar la web se necesita HTTPS, control del servidor y una auditoría de seguridad adicional. Esta edición está publicada desde un historial independiente y depurado; las credenciales de proyectos anteriores deben permanecer revocadas. No utilizar contraseñas de pruebas ni credenciales antiguas en servidores públicos.

## Pruebas

El repositorio incluye workflows de GitHub Actions que ejecutan:

- Validación de sintaxis PHP y JavaScript.
- Integración HTTP de registro, login, logout, recetas y donaciones con MySQL temporal.
- Casos de acceso denegado, CSRF, edición y eliminación por propietario.
- Validaciones de configuración SMTP sin enviar correo externo.

Las interfaces, el almacenamiento y el envío SMTP también se probaron en entorno local con XAMPP. **Las pruebas automáticas no sustituyen una auditoría de seguridad ni garantizan el funcionamiento en un hosting externo.**

## Documentación adicional

- [Configuración de XAMPP y MySQL aislado](docs/configuracion-xampp-portafolio.md)
- [Migración y pruebas de gestión de recetas](docs/fase4-recetas-migracion.md)
- [Configuración de correo SMTP](docs/guia-smtp.md)
- [Pruebas visuales](docs/pruebas-interfaz.md)
- [Pruebas del acceso y registro](docs/pruebas-acceso.md)
- [Seguridad y alcance de la edición de portafolio](docs/SEGURIDAD.md)

---

**AmbienteWebCS** · Proyecto académico de ingeniería de software, recuperado y mejorado para portafolio técnico. El origen del código es académico y colaborativo; antes de distribuirlo con una licencia explícita deben confirmarse los derechos y atribuciones de los autores originales.
