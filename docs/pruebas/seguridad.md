# Pruebas de seguridad (PAI-93)

## Pruebas automáticas

Se ejecutan con `./vendor/bin/sail artisan test`.

| Qué se prueba | Archivo |
|---|---|
| Matriz de acceso: visitante, revendedor, feriante y admin contra las rutas públicas, con sesión y del panel | `tests/Feature/AccesoPorRolTest.php` |
| **Todas** las rutas GET del panel dan login o 403 si no sos admin, y 200 si sos admin | `tests/Feature/SeguridadTest.php` |
| Las acciones del panel (crear, dar de baja, validar, bloquear) dan 403 a un revendedor | `tests/Feature/SeguridadTest.php` |
| XSS: un `<script>` guardado como producto, categoría, feria o feriante se muestra escapado en el catálogo, el perfil y el panel | `tests/Feature/SeguridadTest.php` |
| Ninguna vista usa `{!! !!}` y ningún JS usa `innerHTML` | `tests/Feature/SeguridadTest.php` |
| Inyección SQL y parámetros raros en los filtros del catálogo (no dan 500 ni devuelven todo) | `tests/Feature/CatalogoTest.php` |
| El rol no se puede asignar desde un formulario (registro y `User::create`) | `tests/Feature/RolUsuarioTest.php`, `tests/Feature/Auth/RegistrationTest.php` |
| Bloqueo después de 5 intentos fallidos de login | `tests/Feature/SeguridadSesionTest.php` |
| Límite de 6 registros por minuto (429) | `tests/Feature/SeguridadTest.php` |
| Cabeceras de seguridad y `Cache-Control: no-store` en páginas privadas | `tests/Feature/SeguridadSesionTest.php` |
| Usuario bloqueado: no inicia sesión y es expulsado si ya estaba adentro | `tests/Feature/Admin/UsuarioTest.php` |
| La recuperación de contraseña no revela si un correo está registrado | `tests/Feature/Auth/PasswordResetTest.php` |
| El parámetro `volver` del login no permite redirigir a otro sitio | `tests/Feature/ContactoWhatsappTest.php` |
| Los clics de WhatsApp no guardan el mensaje y se borran a los 12 meses | `tests/Feature/ContactoWhatsappTest.php` |

## Lista de control manual

Completar con la fecha y quién lo probó.

| # | Prueba | Cómo | Resultado esperado | OK | Fecha y quién |
|---|---|---|---|---|---|
| 1 | CSRF | En DevTools, borrar el input `_token` de un formulario (por ejemplo, «Nueva categoría») y enviarlo | Página **419 · Page Expired** | ☐ | |
| 2 | Formularios con `@csrf` | `grep -rL "@csrf" $(grep -rl "<form" resources/views)` y revisar que los que aparezcan sean solo `method="GET"` | Solo formularios GET | ☐ | |
| 3 | Bloqueo por intentos | Ingresar 6 veces una contraseña incorrecta | «Demasiados intentos de acceso. Probá de nuevo en … segundos.» | ☐ | |
| 4 | Cabeceras | DevTools → Network → documento → Response Headers | `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` y `Permissions-Policy` | ☐ | |
| 5 | Atrás después del logout | Entrar al panel, cerrar sesión y tocar Atrás | Pide iniciar sesión; no muestra el panel | ☐ | |
| 6 | Sin `{!! !!}` | `grep -rn "{!!" resources/views` | Sin resultados | ☐ | |
| 7 | Producción | En el `.env` del hosting | `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` con `https://` | ☐ | |
| 8 | HTTPS | Abrir `http://` del sitio publicado | Redirige a `https://` | ☐ | |
| 9 | Secretos | `git log -p | grep -i "password="` y revisar que `.env` no esté en el repo | Nada sensible | ☐ | |
