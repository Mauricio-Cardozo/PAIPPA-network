# Pruebas de seguridad (PAI-93)

Cubre RNF-001 a RNF-005 y RNF-011 de la ERS. Una parte es automática y corre en cada pull request; el resto se revisa a mano antes de cada entrega.

## 1. Pruebas automáticas

`./vendor/bin/sail artisan test --filter=SeguridadTest`

| Prueba | Qué verifica | Requisito |
|---|---|---|
| `un_script_guardado_como_nombre_se_muestra_escapado` | `<script>alert(1)</script>` como nombre de producto y de feriante se ve como texto en el catálogo, en el perfil y en el panel | RNF-002 (XSS) |
| `la_inyeccion_sql_en_los_buscadores_no_rompe_nada` | `' OR 1=1 --`, `DROP TABLE` y `UNION SELECT` en los filtros del catálogo y en el buscador de feriantes responden 200 y no borran datos | RNF-002 (inyección SQL) |
| `ninguna_ruta_del_panel_es_accesible_para_un_comprador_ni_un_visitante` | Recorre **todas** las rutas `admin.*`, con cualquier método: el visitante va al login y el comprador recibe 403. Una ruta nueva sin proteger hace fallar la prueba | RF-003 |
| `todos_los_formularios_que_cambian_datos_llevan_csrf` | Todo `<form method="POST">` de las vistas tiene `@csrf` | RNF-002 (CSRF) |
| `ninguna_vista_imprime_sin_escapar` | No hay `{!! !!}` en las vistas | RNF-002 (XSS) |

Otras pruebas que ya cubrían seguridad:

- `SeguridadSesionTest`: cabeceras de seguridad, páginas privadas sin caché y bloqueo después de 5 intentos fallidos (RNF-003).
- `AccesoPorRolTest` y `VerificarRolTest`: matriz de acceso por rol.
- `Auth\RegistrationTest` y `RolUsuarioTest`: el registro siempre crea compradores y `rol` no es asignable en masa (RN-07).
- `Admin\FerianteTest`: `validado_at` no se puede mandar desde el formulario.
- `ContactoWhatsappTest`: el clic de WhatsApp no guarda el mensaje (RNF-005).

## 2. Checklist manual

Marcar cada punto con la fecha y quién lo revisó.

| # | Qué revisar | Cómo | Resultado esperado | Revisado |
|---|---|---|---|---|
| 1 | CSRF real | En el navegador, DevTools → borrar el campo `_token` de un formulario del panel y enviarlo | Página 419 «La página expiró» | |
| 2 | Bloqueo por intentos | Iniciar sesión 5 veces con una contraseña incorrecta | Al sexto intento, «Demasiados intentos», durante 1 minuto | |
| 3 | Cabeceras de seguridad | DevTools → Network → cualquier página → Response Headers | `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` | |
| 4 | Sin salida cruda | `grep -rn '{!!' resources/views` | Sin resultados | |
| 5 | Producción sin depuración | En el `.env` del hosting | `APP_ENV=production` y `APP_DEBUG=false`; un error muestra la página genérica, sin código | |
| 6 | HTTPS | Abrir el sitio con `http://` | Redirige a `https://` y el candado está activo (RNF-004) | |
| 7 | Contraseñas cifradas | `select password from users limit 1;` en la base | Empieza con `$2y$` (bcrypt, RNF-001) | |
| 8 | Sesión inactiva | Dejar la sesión 120 minutos sin usar (`SESSION_LIFETIME`) | Pide iniciar sesión de nuevo (RNF-011) | |
| 9 | Credenciales fuera del repositorio | `git ls-files | grep -i '\.env$'` | Sin resultados: solo existe `.env.example` (RNF-004) | |
| 10 | Datos personales | Ver una oferta sin sesión y con sesión | Sin sesión no se ve el WhatsApp; con sesión, solo si el feriante lo autorizó (RN-06) | |
