# Manual técnico · PAIPPA network

> Borrador generado a partir del código (PAI-105). El equipo lo revisa y lo completa antes de la entrega.

## 1. Qué es

Plataforma web que publica la oferta de frutas y hortalizas de los feriantes del PAIPPA en Formosa Capital y permite que los revendedores contacten a cada feriante por WhatsApp. El administrador (personal del PAIPPA) carga y valida la información.

## 2. Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | Laravel 13, PHP 8.3 o superior |
| Base de datos | MySQL 8.4 (desarrollo y producción); SQLite en memoria para las pruebas |
| Vistas | Blade, Tailwind CSS 3 y Alpine.js (Laravel Breeze) |
| Mapa | Leaflet 1.9 y tiles de OpenStreetMap (sin clave de API) |
| Compilación del frontend | Vite |
| Pruebas | PHPUnit 12 |
| Entorno de desarrollo | Laravel Sail (Docker) con MySQL y Mailpit |
| Integración continua | GitHub Actions: las pruebas corren en PHP 8.3 y 8.5 en cada push y pull request |

## 3. Arquitectura (MVC)

```
Navegador ──► routes/web.php ──► Middleware ──► Controlador ──► Modelo (Eloquent) ──► MySQL
                                                     │
                                                     └──► Vista Blade (HTML) ──► Navegador
```

- **Rutas** (`routes/web.php`): en tres grupos: públicas, con sesión y del panel (`/admin`).
- **Middleware**: `auth` (sesión), `rol:admin` (`VerificarRol`), `sin-cache` (`SinCache`), y en todas las respuestas web `CabecerasSeguridad` y `UsuarioNoBloqueado`.
- **Controladores** (`app/Http/Controllers`): finos. Los del panel están en `Admin/` y siguen el molde de `CategoriaController` (index, create, store, edit, update, destroy).
- **Validación**: Form Requests en `app/Http/Requests` (nunca en el controlador).
- **Modelos** (`app/Models`): con `SoftDeletes` en las entidades que tienen ABM, relaciones con tipo de retorno y *scopes* para las consultas que se repiten (`Oferta::publicadas()`, `Oferta::filtrar()`, `Feriante::validados()`).
- **Vistas** (`resources/views`): componentes reutilizables (`x-campo`, `x-boton`, `x-tarjeta`, `x-tarjeta-oferta`, `x-boton-whatsapp`, `x-admin.tabla`, `x-admin.baja`) y dos layouts: `x-layouts.publico` y `x-layouts.admin`.

## 4. Roles

| Rol | Qué puede hacer |
|---|---|
| Visitante (sin cuenta) | Ver el catálogo, el perfil de los feriantes y el mapa. No ve teléfonos. |
| Revendedor | Lo mismo, más contactar a los feriantes por WhatsApp. Se registra solo. |
| Feriante | Cuenta opcional que crea el admin («Crear cuenta»): lee y responde los mensajes internos (HU12). |
| Administrador | El panel: ABM de todo, validación de feriantes, bloqueo de usuarios y estadísticas. Se crea desde el seeder o por la base. |

El rol (`users.rol`, enum `App\Enums\Rol`) **no** es asignable en masa: el registro público siempre crea revendedores.

## 5. Modelo de datos

| Tabla | Para qué | Relaciones |
|---|---|---|
| `users` | Usuarios, con `rol`, `telefono`, `bloqueado_at` y `bloqueado_por` | — |
| `categorias` | Frutas, verduras, huevos… | tiene muchos `productos` |
| `productos` | Producto con su unidad de venta (caja, bolsa, kilo…) | pertenece a `categoria` |
| `ferias` | Ferias del PAIPPA con días y horario | tiene muchos `puntos_retiro` y `feriantes` |
| `puntos_retiro` | Dirección, horario y coordenadas | pertenece a `feria` |
| `feriantes` | Productor con su WhatsApp; `validado_at` nulo = pendiente | pertenece a `feria` y, opcionalmente, a `user` |
| `ofertas` | Precio estimado, cantidad disponible y vigencia | pertenece a `feriante`, `producto` y `punto_retiro` |
| `clics_whatsapp` | Interés de un revendedor (sin el texto del mensaje); se borra a los 12 meses | `user`, `feriante`, `oferta` |
| `consultas_producto` | Productos buscados o contactados, una vez por visitante y día | `producto`, `user` |
| `conversaciones` | Una por revendedor y feriante (HU12, opcional) | `revendedor` (user), `feriante` |
| `mensajes` | Texto, remitente y `leido_at` | `conversacion`, `user` |

**Regla central: una oferta está publicada** cuando hoy está entre `vigente_desde` y `vigente_hasta`, tiene cantidad disponible, su feriante está validado, y ni ella ni su feriante, producto o punto de retiro están dados de baja (`Oferta::publicadas()`). El estado del panel (`$oferta->estado`) usa exactamente las mismas reglas.

**Bajas:** todas son lógicas (`deleted_at`). Como un borrado lógico es un `UPDATE`, las claves foráneas no lo frenan: los controladores controlan que no se dé de baja, por ejemplo, una categoría con productos.

**Fechas:** las columnas `date` se guardan como `Y-m-d`, sin hora (ver los accessors de `Oferta` y `ConsultaProducto`), y se comparan contra `today()->toDateString()` para que se use el índice.

## 6. Rutas principales

| Ruta | Quién | Qué hace |
|---|---|---|
| `GET /` | Todos | Catálogo con filtros (`q`, `categoria`, `feria`, `precio_max`) |
| `GET /feriantes/{id}` | Todos | Perfil del feriante (404 si no está validado) |
| `GET /mapa` y `GET /mapa/puntos` | Todos | Mapa y los puntos de retiro en JSON |
| `GET /contacto/whatsapp/{oferta}` | Con sesión | Registra el clic y redirige a `wa.me` |
| `GET /admin` | Admin | Inicio del panel |
| `/admin/categorias`, `productos`, `ferias`, `puntos-retiro`, `feriantes`, `ofertas` | Admin | ABM |
| `PATCH /admin/feriantes/{id}/validar` e `invalidar` | Admin | Valida o invalida a un feriante |
| `PATCH /admin/ofertas/{id}/stock` | Admin | Actualización rápida de cantidad y vigencia |
| `/admin/usuarios` | Admin | Listado y bloqueo |
| `/admin/feriantes/{id}/cuenta` | Admin | Crea la cuenta del feriante y le envía el correo para elegir la contraseña |
| `/mensajes` y `/mensajes/{id}` | Con sesión | Bandeja y conversación (solo los participantes, `ConversacionPolicy`); 10 mensajes por hora |
| `/admin/estadisticas` | Admin | Indicadores por rango de fechas |

La lista completa: `./vendor/bin/sail artisan route:list --except-vendor`.

## 7. Seguridad

- Contraseñas con bcrypt (Laravel), CSRF en todos los formularios y Blade escapa toda la salida (`{{ }}`, nunca `{!! !!}`).
- Consultas con Eloquent y bindings, sin SQL concatenado. Los filtros del catálogo descartan los parámetros inválidos.
- Login: 5 intentos y bloqueo temporal. Registro: 6 por minuto. Contacto: 30 por minuto.
- Cabeceras: `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy` y `Permissions-Policy`.
- Páginas privadas con `Cache-Control: no-store`.
- Usuario bloqueado: no inicia sesión y, si estaba adentro, lo saca el middleware `UsuarioNoBloqueado`.
- Ley 25.326: el teléfono del feriante solo lo usa la redirección a WhatsApp con sesión iniciada; los clics no guardan el mensaje y se borran a los 12 meses (`model:prune`, programado a diario).

El detalle y la lista de control manual están en `docs/pruebas/seguridad.md`.

## 8. Instalación para desarrollo

Ver el `README.md` (Sail o Herd). Resumen con Sail:

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm ci && ./vendor/bin/sail npm run dev
```

Usuarios de prueba (solo en local): `admin@paippa.test` y `test@example.com`, contraseña `password`.
Los correos se ven en Mailpit: <http://localhost:8025>.

## 9. Variables de entorno importantes

| Variable | Desarrollo | Producción |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | **`false`** |
| `APP_URL` | `http://localhost:8080` | `https://dominio-del-sitio` |
| `APP_LOCALE` / `APP_TIMEZONE` | `es` / `America/Argentina/Cordoba` | igual |
| `DB_*` | los de Sail | los del hosting |
| `MAIL_*` | Mailpit (`mailpit:1025`) | el SMTP del hosting |

Nunca se suben credenciales al repositorio: solo van en el `.env` de cada entorno.

## 10. Despliegue en hosting compartido (PHP 8.3+, MySQL, HTTPS)

1. En la PC: `npm ci && npm run build` (genera `public/build`) y `composer install --no-dev --optimize-autoloader`.
2. Subir el proyecto (por Git o por el administrador de archivos). El **document root** del dominio tiene que apuntar a la carpeta `public/`. Si el hosting no lo permite, dejar el proyecto fuera de `public_html` y copiar dentro el contenido de `public/`, ajustando las rutas de `index.php`.
3. Crear la base MySQL en el panel del hosting y completar el `.env` de producción (tabla anterior).
4. Por SSH (si hay): `php artisan key:generate`, `php artisan migrate --force`, `php artisan db:seed --class=CategoriaSeeder --force`, `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache` y `php artisan storage:link`.
5. Crear el administrador real por tinker o por la base (con `rol = admin`); el seeder de usuarios de prueba no corre en producción.
   - Si para la defensa se quieren los **datos de ejemplo** (`FeriaSeeder`), ese seeder usa Faker, que es una dependencia de desarrollo: instalar con `composer install` (sin `--no-dev`) solo para sembrar, o sembrar la base en local y exportarla con `mysqldump`.
6. Programar el cron del hosting cada minuto: `php /ruta/al/proyecto/artisan schedule:run`, que ejecuta `model:prune` a diario.
7. Activar HTTPS (Let's Encrypt suele venir en el panel) y forzar la redirección de `http` a `https`.
8. Revisar la lista de `docs/pruebas/seguridad.md` sobre el sitio publicado.

## 11. Pruebas

```bash
./vendor/bin/sail artisan test
```

Corren en SQLite en memoria y no tocan la base local. Hay pruebas de cada criterio de aceptación, de acceso por rol, de seguridad y de rendimiento sin N+1. Rendimiento con 2000 ofertas: `docs/pruebas/rendimiento.md`.

## 12. Pendientes conocidos

- **HU12 (mensajería interna):** es opcional y está en la rama `feature/hu12-mensajeria`. Se integra solo si el MVP está probado.
- Los datos de los seeders son de **ejemplo**: se reemplazan por los reales del relevamiento con el PAIPPA (PAI-11).
- Falta medir el rendimiento en MySQL (`docs/pruebas/rendimiento.md`).
- Los feriantes no tienen cuenta propia: el administrador carga sus ofertas con los datos que ellos le pasan.
