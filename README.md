# E-Commerce PHP Platform (Minimal scaffold)

Contenido:
- composer.json (dependencias)
- public/index.php (entrada, router FastRoute)
- src/ (clases: DB, Auth, Product, Invoice)
- views/ (plantillas simples)
- create_db.php (crea la BD y tablas)
- admin_upload.php (panel admin básico para subir/editar productos)
- scripts/ (logs)
- .htaccess (rewrite to public/index.php for Apache)

INSTRUCCIONES RÁPIDAS:
1. Coloca este proyecto en tu servidor (document root apuntando a `public/`) o usa PHP built-in server:
   `php -S localhost:8000 -t public`
2. Ejecuta `composer install` en la raíz del proyecto para instalar dependencias.
3. Copia `config.example.php` a `config.php` y ajusta las credenciales de la base de datos y PayPal.
4. Accede a `create_db.php` en el navegador para crear la base de datos y tablas (o ejecútalo por CLI `php create_db.php`).
5. Admin: `admin_upload.php` (se autentica con credenciales definidas en la tabla `users`).
6. El checkout usa PayPal sandbox; revisa `src/PayPalClient.php` y configura CLIENT_ID/SECRET.
7. PHPMailer es usado para enviar correo y adjuntar factura generada por FPDF.

SEGURIDAD (implementado en el código):
- PDO con prepared statements.
- password_hash()/password_verify() para contraseñas.
- CSRF tokens en formularios críticos.
- Sanitización y validación de uploads.
- HttpOnly y Secure flags en cookies (configurar Secure tras habilitar HTTPS).
- Logs básicos en `scripts/logs.txt`.

NOTA: Esto es un scaffold educativo. Revísalo y adapta antes de usar en producción. El SSL lo gestionas tú (según tu petición).
