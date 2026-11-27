# Correo (PAI-86)

El sistema manda correos para recuperar la contraseña (HU14, RF-016). La configuración cambia según el entorno.

## En desarrollo: Mailpit

Mailpit es un servidor de correo de prueba que viene como contenedor de Sail (`compose.yaml`). No manda nada a internet: guarda los correos y los muestra en una página.

1. En tu `.env` usá los valores de `.env.example`:

   ```dotenv
   MAIL_MAILER=smtp
   MAIL_HOST=mailpit
   MAIL_PORT=1025
   ```

2. Levantá los contenedores (la primera vez descarga la imagen de Mailpit):

   ```bash
   ./vendor/bin/sail up -d
   ```

3. Probá «¿Olvidaste tu contraseña?» en `/forgot-password` y abrí **http://localhost:8025**: el correo aparece ahí.

Si no usás Sail (por ejemplo, con `php artisan serve`), dejá `MAIL_MAILER=log`: el correo se escribe en `storage/logs/laravel.log`.

## En las pruebas

`phpunit.xml` usa `MAIL_MAILER=array`: los correos quedan en memoria y las pruebas no necesitan Mailpit.

## En producción: el SMTP del hosting

En el `.env` del servidor (nunca en el repositorio):

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=<servidor SMTP que da el hosting>
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=<casilla creada en el hosting>
MAIL_PASSWORD=<contraseña de esa casilla>
MAIL_FROM_ADDRESS=<la misma casilla>
MAIL_FROM_NAME="PAIPPA network"
```

Los datos del servidor SMTP salen del panel del hosting. Después de cambiar el `.env`, correr `php artisan config:cache`.
