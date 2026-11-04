# Plan de desarrollo con OpenCode · PAIPPA network

Este archivo es la especificación que lee OpenCode con `/tarea PAI-xx`. Hay una sección por subtarea de Jira, en el orden en que conviene hacerlas.

## Cómo se usa

### Una sola vez, en cada PC (dentro de WSL)
```bash
curl -fsSL https://opencode.ai/install | bash     # o: npm i -g opencode-ai
cd ~/PAIPPA-network
opencode                                          # dentro: /models para elegir el modelo
```
No hace falta ejecutar `/init`: el `AGENTS.md` ya está escrito. Después de mergear este plan a `develop`, cerrá y volvé a abrir OpenCode para que cargue los comandos `/tarea`, `/revisar` y `/explicar`.

### El ciclo de cada subtarea

| Paso | Quién | Qué |
|---|---|---|
| 1 | Vos | En Jira, mover la subtarea a **En curso** |
| 2 | Vos | `git switch develop && git pull && git switch -c feature/PAI-xx-descripcion` |
| 3 | Vos | En OpenCode: `/tarea PAI-xx` |
| 4 | OpenCode | Muestra el plan y **espera tu OK**: leelo, corregilo o aprobalo |
| 5 | OpenCode | Programa, escribe las pruebas y ejecuta `pint` y `test` |
| 6 | Vos | Probar a mano en el navegador lo que dice el resumen |
| 7 | Vos | `/revisar`: OpenCode revisa el diff contra las reglas, sin tocar nada |
| 8 | Vos | Commit (OpenCode lo propone y vos aprobás) y `git push -u origin feature/PAI-xx-descripcion` |
| 9 | Vos | Abrir el PR a `develop` con el texto que propuso. Lo aprueba otro integrante |
| 10 | Vos | Merge, mover la subtarea a **Finalizado** en Jira y tildarla en la hoja |
| 11 | Vos | `/explicar PAI-xx` para estudiarlo para la defensa |

> **Importante:**
> - La defensa es individual y sobre todo el sistema. Lo que escribe OpenCode lo tenés que poder explicar vos; para eso está `/explicar`.
> - Confirmen con el docente si hay una regla sobre el uso de IA, y declárenlo en el informe final, en la sección de aspectos éticos (Unidad 5).

### Lo que **no** es para OpenCode

Lo hace el equipo:
- El relevamiento, la ERS, el DER, los mockups, la prueba de usabilidad con usuarios y el video.
- Los informes, el despliegue real en el hosting y las releases.
- Aprobar PRs y mover Jira.
- PAI-77 y PAI-102: las pruebas en un celular real.

OpenCode puede ayudar a **redactar** el manual técnico y el README a partir del código.

---

## Modelo de datos propuesto

> **Es una propuesta para arrancar.** Cuando Mauricio cierre el DER (PAI-18), se corrige esta sección y se guardan `docs/DER.png` y `docs/diccionario.xlsx`. Si el DER dice otra cosa, manda el DER.

| Tabla | Columnas principales | Relaciones |
|---|---|---|
| `users` (agregar) | `rol` (`admin`, `feriante`, `revendedor`; por defecto `revendedor`) · `telefono` nullable · `bloqueado_at` nullable · `bloqueado_por` nullable (FK `users`) | |
| `ferias` | `nombre` · `descripcion` nullable · `dias_horario` · soft deletes | tiene muchos `puntos_retiro` y muchos `feriantes` |
| `puntos_retiro` | `feria_id` · `nombre` · `direccion` · `horario` · `latitud` decimal(10,7) · `longitud` decimal(10,7) · soft deletes | pertenece a `feria` |
| `feriantes` | `user_id` nullable, único (cuenta opcional) · `feria_id` · `nombre` · `telefono_whatsapp` (formato `549XXXXXXXXXX`) · `descripcion` nullable · `validado_at` nullable · soft deletes | pertenece a `feria` y a `user`; tiene muchas `ofertas` |
| `categorias` | `nombre` único · soft deletes | tiene muchos `productos` |
| `productos` | `categoria_id` (restrict) · `nombre` · `unidad` (por defecto «caja») · `descripcion` nullable · soft deletes | pertenece a `categoria` |
| `ofertas` | `feriante_id` · `producto_id` · `punto_retiro_id` · `precio_caja` decimal(10,2) · `cajas_disponibles` unsigned · `vigente_desde` date · `vigente_hasta` date · soft deletes | pertenece a `feriante`, `producto` y `punto_retiro` |
| `clics_whatsapp` | `user_id` · `feriante_id` · `oferta_id` nullable · `created_at` | sin contenido del mensaje; se borra a los 12 meses |
| `consultas_producto` | `producto_id` · `user_id` nullable · `origen` (`busqueda`, `whatsapp`) · `created_at` | para HU15 |
| `conversaciones` (HU12, opcional) | `revendedor_id` (FK `users`) · `feriante_id` | tiene muchos `mensajes` |
| `mensajes` (HU12, opcional) | `conversacion_id` · `user_id` (remitente) · `cuerpo` · `leido_at` nullable | |

**Únicos:** `categorias.nombre` y `ferias.nombre` son únicos solo entre los registros activos: validar con `Rule::unique(...)->withoutTrashed()`. Ojo: `unique(['nombre', 'deleted_at'])` **no** alcanza en MySQL/SQLite, porque los `NULL` se consideran distintos y permiten duplicados activos. Si se quiere reforzar en la base, agregar una columna generada `activo` (`1` en activos, `NULL` en dados de baja) y `unique(['nombre', 'activo'])`. `conversaciones` tiene un único par (`revendedor_id`, `feriante_id`).

**Política de cada clave foránea** (definida antes de migrar):

| Clave foránea | `onDelete` | Por qué |
|---|---|---|
| `puntos_retiro.feria_id` | `restrict` | Una feria con puntos no se borra |
| `feriantes.feria_id` | `restrict` | Ídem |
| `feriantes.user_id` | `nullOnDelete` | Si se borra la cuenta, el feriante queda sin cuenta |
| `productos.categoria_id` | `restrict` | |
| `ofertas.feriante_id`, `ofertas.producto_id`, `ofertas.punto_retiro_id` | `restrict` | Las ofertas son historial |
| `clics_whatsapp.user_id`, `clics_whatsapp.feriante_id` | `cascade` | Son registros derivados |
| `clics_whatsapp.oferta_id` | `nullOnDelete` | |
| `consultas_producto.producto_id` | `cascade` | |
| `consultas_producto.user_id` | `nullOnDelete` | |
| `users.bloqueado_por` | `nullOnDelete` | |
| `mensajes.conversacion_id` | `cascade` | |
| `conversaciones.revendedor_id`, `conversaciones.feriante_id` | `cascade` | |

Recordá que, como casi todo usa borrado lógico, estas reglas actúan solo si alguien borra de verdad. Las bajas del ABM se controlan en el código (ver `AGENTS.md`).

> **Por confirmar con el relevamiento:** el modelo supone que cada feriante está en **una sola** feria (`feriantes.feria_id`). Si un feriante puede estar en varias, hace falta una tabla pivote `feria_feriante`, y hay que decidirlo antes del esquema.

**Reglas de negocio:**
- Una oferta está **publicada** cuando su feriante está validado (`validado_at` no nulo), hoy está entre `vigente_desde` y `vigente_hasta`, tiene `cajas_disponibles > 0` y ninguno de sus registros está borrado.
- Para contactar por WhatsApp o enviar mensajes hay que tener una sesión iniciada.

---

## Sprint 1 · Base de datos, acceso y layout (5/11 → 13/11)

> **Antes de la primera tarea:**
> - Este plan, el `AGENTS.md` y los comandos tienen que estar mergeados en `develop` (PR de PAI-29).
> - El DER (PAI-18) congelado antes de PAI-53 y del esquema.
> - Los mockups (PAI-25) antes de PAI-36 y PAI-46. Si no están, esas dos se hacen con el diseño de Breeze provisional y se ajustan después.
>
> **Camino crítico:** 35 → 39 → 46 → 54 → 48 → 49 → 57 → 58 · 53 → esquema → 56 → 57 · 74 → 75 → 76 → 60. Si PAI-46 no termina en el Sprint 1, PAI-54 no arranca y se cae el Sprint 2.
>
> **Reparto del Sprint 1 (a confirmar en el grupo):** con lo que hay en Jira, Mauricio tiene 9 tareas, Lucas 1 y Apolo 2, y Mauricio queda como cuello de botella. La propuesta:
>
> | Quién | Orden |
> |---|---|
> | Mauricio | PAI-35 → PAI-37 → PAI-39 → PAI-40 → PAI-43 → PAI-44 → PAI-45 |
> | Lucas | PAI-41 (día 1) → PAI-53 → esquema |
> | Apolo | PAI-36 (después de PAI-37 y los mockups) → PAI-46 (después de PAI-39 y los mockups) |
>
> Si se acepta, cambiar la etiqueta y el responsable de PAI-53 y del esquema en Jira.

### PAI-35 · Migración de usuarios con rol
- **Quién:** Mauricio · **HU1** · **Depende de:** nada
- **Hacer:**
  - Enum `App\Enums\Rol` (`Admin = 'admin'`, `Feriante = 'feriante'`, `Revendedor = 'revendedor'`) con `etiqueta()`.
  - Migración **nueva** `add_rol_to_users_table` con `rol` (string, por defecto `revendedor`, con índice), `telefono` nullable, `bloqueado_at` nullable y `bloqueado_por` (FK nullable a `users`, `nullOnDelete`).
  - En `User`: cast `rol => Rol::class` y los helpers `esAdmin()`, `esRevendedor()` y `esFeriante()`. **No** agregar `rol` a `#[Fillable]`.
  - En `UserFactory`: estados `admin()` y `feriante()` con `state(['rol' => Rol::Admin])` (las factories no respetan `fillable`, así que funciona).
  - En `DatabaseSeeder`: el usuario `test@example.com` que trae Laravel y un admin `admin@paippa.test`, los dos con contraseña `password` y **solo** si `app()->isLocal()`. En el seeder se asigna el rol con `forceFill`.
- **Criterios:** un usuario nuevo es `revendedor` por defecto; `User::factory()->admin()` crea un admin; `User::create(['rol' => 'admin', ...])` **no** crea un admin.
- **Pruebas:** `tests/Feature/RolUsuarioTest.php`.

### PAI-53 · Migraciones, modelos y seeders de productos y categorías
- **Quién:** Mauricio (o Lucas, ver la nota del reparto) · **HU5** · **Depende de:** PAI-18 (DER)
- **Hacer:**
  - Migraciones de `categorias` y `productos`, según el modelo de datos.
  - Modelos `Categoria` y `Producto` con `$table`, `SoftDeletes`, relaciones y factories.
  - `CategoriaSeeder` y `ProductoSeeder` con datos de ejemplo: frutas, verduras, huevos, lácteos y miel. Cuando esté el relevamiento (PAI-11), se reemplazan por los reales.
- **Criterios:** `migrate:fresh --seed` anda; `$categoria->productos` devuelve sus productos.
- **Pruebas:** relaciones en `tests/Unit` o `tests/Feature/ModelosTest.php`.

### PAI-xx (crear en Jira) · Esquema de ferias, puntos de retiro, feriantes y ofertas
> No está en Jira. Corresponde a las tareas 2.1 y 2.2 del plan aprobado. Creá la subtarea dentro de HU4 (Mauricio, Sprint 1) y reemplazá `PAI-xx` por la clave real en este título y en las dependencias de PAI-48, PAI-56 y PAI-70. Es la tarea que desbloquea casi todo el Sprint 2.
- **Quién:** Mauricio (o Lucas, ver la nota del reparto) · **HU4 y HU6** · **Depende de:** PAI-53 y PAI-18 (DER)
- **Hacer:**
  - Migraciones de `ferias`, `puntos_retiro`, `feriantes` y `ofertas`, en ese orden, según el modelo de datos y la tabla de políticas de claves foráneas.
  - Modelos con `$table`, `SoftDeletes`, casts (`validado_at` datetime; `vigente_desde` y `vigente_hasta` date; `precio_caja` `decimal:2`) y todas las relaciones.
  - Factories. Seeders: 3 ferias, 6 puntos de retiro con coordenadas reales de Formosa Capital (alrededor de -26.18, -58.17), 10 feriantes (8 validados) y 40 ofertas.
- **Criterios:** `migrate:fresh --seed` anda en las tres PCs; `$feriante->ofertas`, `$oferta->puntoRetiro->feria` y `$feria->puntosRetiro` funcionan.
- **Pruebas:** relaciones y claves foráneas (no se puede borrar una feria con puntos de retiro).

### PAI-37 · Controlador de registro: rol Revendedor por defecto
- **Quién:** Mauricio · **HU1** · **Depende de:** PAI-35 y PAI-41
- **Hacer:**
  - Agregar `telefono` a `#[Fillable]` en `User` (es un dato que edita el usuario).
  - En `RegisteredUserController@store`, crear el usuario con `rol = Rol::Revendedor` asignado en el código (no en el array de `create`). Guardar `telefono` si viene.
  - Validar con un Form Request (`RegistroRequest`).
  - Agregar `throttle:6,1` a la ruta `POST /register`.
- **Criterios:** con datos válidos se crea la cuenta como revendedor y se inicia sesión; aunque el formulario mande `rol=admin`, queda revendedor; un correo repetido muestra el error.
- **Pruebas:** ampliar `tests/Feature/Auth/RegistrationTest.php`.

### PAI-36 · Formulario de registro con validaciones (vista según mockup)
- **Quién:** Apolo · **HU1** · **Depende de:** PAI-37, PAI-41 y los mockups (PAI-25)
- **Hacer:** adaptar `resources/views/auth/register.blade.php` al mockup: nombre, correo, teléfono (opcional), contraseña y confirmación, con etiquetas en español, errores debajo de cada campo y diseño mobile-first.
- **Criterios:** se ve bien en 320 y 375 px; los errores se muestran en español; los valores se conservan si hay error.
- **Pruebas:** la vista carga (200) y muestra los campos.

### PAI-39 · Autenticación de Laravel (Breeze) y redirección por rol
- **Quién:** Mauricio · **HU2** · **Depende de:** PAI-35
- **Hacer:**
  - Crear las rutas base con nombre: `catalogo` (`/`, por ahora con una vista simple) y `admin.inicio` (`/admin`).
  - La ruta `dashboard` **se queda** (la usan seis controladores y varias pruebas de Breeze), pero pasa a ser un redirector: el admin va a `admin.inicio` y el resto a `catalogo`. Así el login, el registro, la verificación de correo y la confirmación de contraseña siguen andando sin tocarlos.
  - Se puede borrar `dashboard.blade.php` y hay que actualizar el enlace de `navigation.blade.php`.
- **Criterios:** el admin cae en `/admin` y el revendedor en `/`; las 25 pruebas de Breeze siguen pasando (con los cambios mínimos en las que esperan `/dashboard`).
- **Pruebas:** `tests/Feature/Auth/AuthenticationTest.php` con los dos casos.

### PAI-40 · Rutas privadas y cierre de sesión
- **Quién:** Mauricio · **HU2** · **Depende de:** PAI-39
- **Hacer:**
  - Ordenar `routes/web.php` en tres grupos comentados: públicas (catálogo, perfil del feriante, mapa), con sesión (perfil propio, contacto) y admin (`prefix('admin')->name('admin.')`).
  - El logout invalida la sesión y vuelve a `/`.
  - Middleware `SinCache` en el grupo con sesión: cabecera `Cache-Control: no-store, private`. Sin eso, el navegador muestra la página privada desde la caché al tocar Atrás, aunque la sesión ya no exista.
- **Criterios:** un visitante que entra a `/profile` o `/admin` va al login; después del logout no se puede volver con el botón Atrás a una página privada.
- **Pruebas:** visitante redirigido, logout y la cabecera `Cache-Control` en una ruta privada.

### PAI-43 · Middleware de roles y protección de rutas
- **Quién:** Mauricio · **HU3** · **Depende de:** PAI-40
- **Hacer:**
  - Middleware `App\Http\Middleware\VerificarRol`, registrado como alias `rol` en `bootstrap/app.php`. Se usa así: `->middleware('rol:admin')`.
  - Si el rol no coincide, responde 403, con la vista `resources/views/errors/403.blade.php` en español.
- **Criterios:** un revendedor que entra a `/admin` recibe 403; el admin entra.
- **Pruebas:** en PAI-45.

### PAI-44 · Límite de intentos de acceso y CSRF en formularios
- **Quién:** Mauricio · **HU3** · **Depende de:** PAI-43 y PAI-41
- **Hacer:**
  - Middleware `CabecerasSeguridad` en el grupo `web` (en `bootstrap/app.php`): `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin` y `Permissions-Policy: camera=(), microphone=(), geolocation=()`. Sin CSP por ahora: si se agrega, tiene que permitir OpenStreetMap y Vite.
  - Verificar el límite de 5 intentos de `LoginRequest` (viene en Breeze) y que el mensaje esté en español.
  - Revisar que todos los formularios tengan `@csrf` y que no haya rutas excluidas de CSRF.
- **Criterios:** al sexto intento fallido aparece «Demasiados intentos…»; no hay formularios sin `@csrf`.
- **Pruebas:** bloqueo por intentos y las cuatro cabeceras. Ojo: en las pruebas automáticas Laravel no verifica el CSRF, así que eso se revisa en el código y a mano en PAI-93.

### PAI-45 · Pruebas automáticas de acceso por rol
- **Quién:** Mauricio · **HU3** · **Depende de:** PAI-43
- **Hacer:** `tests/Feature/AccesoPorRolTest.php` con una matriz de visitante, revendedor, feriante y admin contra las rutas públicas, las de sesión y las de admin. Usar `#[DataProvider('casos')]` con un método `public static function casos(): array` (en PHPUnit 12 el `@dataProvider` del docblock no funciona).
- **Criterios:** cada combinación da lo esperado: 200, redirección al login o 403.

### PAI-41 · Mensajes de error y validación en español (y zona horaria)
- **Quién:** Lucas · **HU2** · **Depende de:** nada · **Hacerla el día 1:** de ella dependen PAI-36, PAI-37, PAI-44 y PAI-87.
- **Hacer:**
  - `sail artisan lang:publish` (publica `lang/en`), y después crear `lang/es/auth.php`, `passwords.php`, `pagination.php` y `validation.php`, traducidos a mano (sin paquetes).
  - Crear `lang/es.json` con los textos de Breeze: login, registro, perfil, verificación **y las líneas del correo de recuperación de contraseña** («Reset Password Notification», «You are receiving this email…», etc.).
  - En `config/app.php`: `'timezone' => env('APP_TIMEZONE', 'UTC')`, y en `.env.example`: `APP_TIMEZONE=America/Argentina/Cordoba`. Avisar a los tres que lo agreguen a su `.env`.
  - En `AppServiceProvider::boot()`: `Carbon::setLocale(config('app.locale'))`, para que `diffForHumans()` diga «hace 2 horas».
- **Criterios:** todos los errores de validación y de login, los textos de Breeze y el correo de recuperación aparecen en español; `now()` está en la hora de Argentina.
- **Pruebas:** un registro con un correo inválido devuelve el mensaje en español; `now()->diffForHumans()` está en español.

### PAI-46 · Layout Blade base y menú según rol (tarea 3.1)
- **Quién:** Apolo · **HU3** · **Depende de:** PAI-39 y los mockups (si no están, con el diseño de Breeze y se ajusta después)
- **Hacer:**
  - Un layout público (`x-layouts.publico`: encabezado, menú, pie) y el layout del panel admin (`x-layouts.admin`), a partir de los de Breeze.
  - Componentes nuevos: `x-alerta` (mensajes flash), `x-boton`, `x-tarjeta` y `x-campo` (etiqueta, input y error).
  - Paleta y tipografía del mockup en `tailwind.config.js`.
  - Menú según el rol: el visitante ve «Ingresar» y «Registrarse», el revendedor «Catálogo», «Mapa» y su cuenta, y el admin el panel.
- **Criterios:** se ve bien en 320, 375 y 768 px y en escritorio; el menú muestra solo lo del rol; todas las vistas de Breeze usan el layout nuevo.
- **Pruebas:** el menú del admin contiene «Panel» y el del revendedor no.

---

## Sprint 2 · Panel del administrador y contacto (14/11 → 18/11)

### PAI-54 · ABM de categorías y productos (molde de los demás ABM)
- **Quién:** Lucas · **HU5** · **Depende de:** PAI-53, PAI-43 y PAI-46
- **Hacer:**
  - `Admin\CategoriaController` y `Admin\ProductoController` con `Route::resource` (sin `show`) dentro del grupo admin.
  - Form Requests para crear y editar. Vistas `index`, `create` y `edit` con el layout admin.
  - Paginación de 15, búsqueda por nombre, mensajes flash y baja lógica.
  - No se puede dar de baja una categoría que tiene productos activos. Como es borrado lógico, la clave foránea no lo frena: chequear `$categoria->productos()->exists()` en el controlador y mostrar un mensaje.
- **Criterios:** el admin crea, edita, lista y da de baja; un revendedor recibe 403; el nombre de la categoría es único entre las activas; no se puede dar de baja una categoría con productos.
- **Pruebas:** `tests/Feature/Admin/CategoriaTest.php` y `ProductoTest.php`.
- **Nota:** este ABM es el **molde**. Los demás copian su estructura, así que conviene que quede muy prolijo y comentado.

### PAI-48 · ABM de ferias y puntos de retiro (con latitud y longitud)
- **Quién:** Lucas · **HU4** · **Depende de:** PAI-xx (esquema) y PAI-54
- **Hacer:**
  - Igual que el molde, para ferias y puntos de retiro.
  - El punto de retiro tiene un selector de feria, dirección, horario, latitud (entre -90 y 90) y longitud (entre -180 y 180).
  - Una ayuda en el formulario: «Copiá las coordenadas desde Google Maps (clic derecho en el lugar)».
- **Criterios:** no se aceptan coordenadas inválidas; no se puede dar de baja una feria con puntos de retiro o feriantes activos (chequeo en el controlador, igual que PAI-54).
- **Pruebas:** ABM y validación de coordenadas.

### PAI-49 · ABM de feriantes con estado validado
- **Quién:** Lucas · **HU4** · **Depende de:** PAI-48
- **Hacer:**
  - ABM de feriantes con feria, nombre, `telefono_whatsapp` y descripción. El teléfono se **normaliza a solo dígitos** antes de validar (en `prepareForValidation()` del Form Request) y después se valida con `^549\d{10}$`. Así `+54 9 370 412-3456` pasa y se guarda como `5493704123456`.
  - Acciones `PATCH admin/feriantes/{feriante}/validar` y `.../invalidar`, que guardan o limpian `validado_at`.
  - Filtro «Pendientes de validar».
- **Criterios:** un feriante nuevo queda sin validar; solo el admin valida.
- **Pruebas:** validar, invalidar y el teléfono con espacios, guiones y `+`.

### PAI-56 · Modelo de oferta y scope de vigentes
- **Quién:** Mauricio · **HU6** · **Depende de:** PAI-xx (esquema)
- **Hacer:**
  - En `Oferta`, el scope `vigentes()` (hoy entre las fechas y `cajas_disponibles > 0`) y el scope `publicadas()` (vigentes, de feriante validado, y sin feriante, producto ni punto de retiro borrados).
  - El accessor `estado` (enum `EstadoOferta`: Publicada, Vencida, Sin stock, Feriante sin validar) con **las mismas reglas** que `publicadas()`, para que el panel y el catálogo nunca se contradigan.
  - Comparar fechas como dice `AGENTS.md` (sin `whereDate`).
- **Criterios:** las reglas de negocio del modelo de datos.
- **Pruebas:** `tests/Feature/OfertaScopesTest.php`, con un caso por cada regla, los bordes (vence hoy, empieza mañana) con `travelTo`, y que `estado` coincida con `publicadas()`.

### PAI-51 · Prueba: un feriante sin validar no aparece en el catálogo
- **Quién:** Mauricio · **HU4** · **Depende de:** PAI-56
- **Hacer:** prueba de que las ofertas de un feriante sin validar no aparecen en `Oferta::publicadas()`. Agregar un `@todo` para extenderla a la vista del catálogo en PAI-61.

### PAI-57 · Formulario de carga de ofertas
- **Quién:** Lucas · **HU6** · **Depende de:** PAI-56 y PAI-49
- **Hacer:**
  - ABM `admin.ofertas` con selectores de feriante (solo validados), producto y punto de retiro (de la feria del feriante).
  - Validar: precio mayor a 0, cajas desde 0, «hasta» igual o posterior a «desde».
  - El listado muestra el accessor `estado` de PAI-56 (no se calcula en la vista).
- **Criterios:** los de HU6 en Jira.
- **Pruebas:** creación válida e inválida.

### PAI-58 · Actualización de cajas disponibles y vigencia
- **Quién:** Lucas · **HU6** · **Depende de:** PAI-57
- **Hacer:**
  - Una acción rápida desde el listado (`PATCH admin/ofertas/{oferta}/stock`) para cambiar las cajas y la fecha «hasta» sin abrir el formulario entero.
  - Mostrar «Actualizado hace X» con `updated_at`.
- **Pruebas:** la actualización y su validación.

### PAI-50 · Vistas de formularios y listados del panel
- **Quién:** Apolo · **HU4** · **Depende de:** PAI-54, PAI-48 y PAI-49
- **Hacer:** unificar las vistas del panel con los componentes del layout. Los listados son tablas en escritorio y tarjetas en el celular. Confirmar antes de dar de baja (modal de Breeze, no `confirm()`). Estados vacíos («Todavía no hay ofertas»).
- **Criterios:** todo el panel se ve igual y funciona en 375 px.

### PAI-74 · Generación del enlace wa.me con mensaje prearmado
- **Quién:** Lucas · **HU11** · **Depende de:** PAI-56
- **Hacer:** la clase `App\Support\WhatsApp` con `enlace(Feriante $f, ?Oferta $o): string`. Devuelve `https://wa.me/{telefono}?text=` con `rawurlencode`. Ejemplo de mensaje: «Hola {feriante}, vi en PAIPPA network tu oferta de {producto} a ${precio} por caja (retiro en {punto}). ¿Sigue disponible?».
- **Pruebas:** unitaria, con acentos y caracteres especiales bien codificados.

### PAI-75 · Registro del clic (usuario, feriante, oferta y fecha)
- **Quién:** Lucas · **HU11** · **Depende de:** PAI-74
- **Hacer:**
  - Migración y modelo `ClicWhatsapp` (`$table = 'clics_whatsapp'`, solo `created_at` con `useCurrent()`, y `const UPDATED_AT = null;` en el modelo).
  - Ruta `GET /contacto/whatsapp/{oferta}` con sesión iniciada: registra el clic y redirige con `redirect()->away(...)`.
  - El modelo es `Prunable`: borra los registros de más de 12 meses. Agregar `Schedule::command('model:prune')->daily()` en `routes/console.php`.
- **Criterios:** un visitante va al login; el clic queda registrado sin el texto del mensaje; una oferta no publicada da 404.
- **Pruebas:** los tres criterios y el prune.

### PAI-76 · Botones de WhatsApp en tarjetas y perfil
- **Quién:** Apolo · **HU11** · **Depende de:** PAI-75 y PAI-46
- **Hacer:** el componente `x-boton-whatsapp :oferta="$oferta"`. Con sesión, enlaza a la ruta de PAI-75; sin sesión, muestra «Iniciá sesión para contactar» y lleva al login guardando la página actual (`redirect()->guest(...)` o `url.intended`), así después del login vuelve a la oferta y no al catálogo. Se usa en las tarjetas del catálogo y en el perfil, en el Sprint 3.
- **Pruebas:** se renderiza distinto con sesión y sin sesión.

---

## Sprint 3 · Catálogo, mapa y MVP (19/11 → 24/11)

### PAI-61 · Consulta de ofertas vigentes con Eloquent
- **Quién:** Lucas · **HU7** · **Depende de:** PAI-56 · **Adelantarla a fines del Sprint 2**, apenas esté PAI-56, para que PAI-60 y PAI-63 arranquen el primer día del Sprint 3.
- **Hacer:** `CatalogoController@index` en la ruta `catalogo` (`/`), con `Oferta::publicadas()->with(['producto.categoria', 'feriante.feria', 'puntoRetiro'])->latest('updated_at')->paginate(12)`.
- **Criterios:** solo aparecen ofertas publicadas; sin N+1.
- **Pruebas:** extender PAI-51 a la vista y contar las consultas a la base.

### PAI-60 · Vista pública del catálogo con tarjetas de oferta
- **Quién:** Apolo · **HU7** · **Depende de:** PAI-61, PAI-76 y PAI-68 (para el enlace al perfil; si todavía no está, dejar el nombre sin enlace y agregarlo después)
- **Hacer:** una grilla de `x-tarjeta-oferta` con producto, categoría, precio por caja (formato `$ 12.500,00`), cajas disponibles, feriante (enlace al perfil), punto de retiro, «actualizado hace X» y el botón de WhatsApp. Paginación en español.
- **Criterios:** una columna en 320 px, dos en 768 px y tres o cuatro en escritorio.

### PAI-63 · Formulario de filtros
- **Quién:** Apolo · **HU8** · **Depende de:** PAI-60
- **Hacer:** un formulario `GET` con texto (producto), categoría, feria y precio máximo. Conserva los valores elegidos y tiene un botón «Limpiar». En el celular, se pliega con Alpine.

### PAI-64 · Consultas con parámetros y paginación
- **Quién:** Lucas · **HU8** · **Depende de:** PAI-61
- **Hacer:** el scope `Oferta::filtrar(array $filtros)` con `when()` para cada filtro. Validar los parámetros con `CatalogoRequest` y paginar con `withQueryString()`.
- **Criterios:** los filtros se combinan; un parámetro raro (`?categoria=abc`, `?q=' OR 1=1`) no rompe nada ni devuelve 500.
- **Pruebas:** cada filtro, combinaciones y parámetros maliciosos.

### PAI-65 · Índices y medición de tiempo de respuesta (< 3 s)
- **Quién:** Mauricio · **HU8** · **Depende de:** PAI-64
- **Hacer:**
  - **Primero medir:** `VolumenSeeder` (no se llama por defecto) con 2000 ofertas, y anotar en `docs/pruebas/rendimiento.md` el tiempo de respuesta y el `EXPLAIN` de las consultas reales del catálogo filtrado.
  - **Después indexar según lo medido:** una migración nueva con índices compuestos en `ofertas` que sigan la consulta real (por ejemplo, `vigente_hasta` + `cajas_disponibles`, `producto_id`, `feriante_id`) y en `feriantes.validado_at`.
  - Ojo: un índice común en `productos.nombre` **no** ayuda a `LIKE '%texto%'`. Si la búsqueda por texto es lenta, evaluar `LIKE 'texto%'` o un índice `FULLTEXT` (solo MySQL: la prueba en SQLite tiene que seguir andando).
  - Volver a medir y anotar el después.
- **Criterios:** el catálogo filtrado responde en menos de 3 s con 2000 ofertas.

### PAI-68 · Controlador del perfil con eager loading
- **Quién:** Lucas · **HU9** · **Depende de:** PAI-61
- **Hacer:** la ruta `feriantes.show` (`/feriantes/{feriante}`), solo para feriantes validados (404 si no lo está). Carga la feria, las ofertas publicadas con su producto y los puntos de retiro.
- **Pruebas:** validado da 200 y no validado da 404; sin N+1.

### PAI-67 · Vista de perfil del feriante
- **Quién:** Apolo · **HU9** · **Depende de:** PAI-68, PAI-60 (reusa `x-tarjeta-oferta`) y PAI-74
- **Hacer:** datos del feriante, feria, puntos de retiro con su horario, sus ofertas (reusa `x-tarjeta-oferta`) y el botón de WhatsApp general.

### PAI-70 · Ruta que devuelve los puntos de retiro en JSON
- **Quién:** Lucas · **HU10** · **Depende de:** PAI-xx (esquema)
- **Hacer:** `GET /mapa/puntos` con un `PuntoRetiroResource` (`id`, `nombre`, `direccion`, `horario`, `latitud`, `longitud`, `feria` y la cantidad de ofertas publicadas).
- **Pruebas:** la estructura del JSON y que no incluya puntos borrados.

### PAI-71 · Mapa con Leaflet y OpenStreetMap
- **Quién:** Apolo · **HU10** · **Depende de:** PAI-70
- **Hacer:**
  - `npm install leaflet` (pre-aprobado).
  - `resources/js/mapa.js` importa Leaflet y su CSS. La vista `mapa` (`/mapa`) tiene un `<div id="mapa">`.
  - Centro en Formosa Capital (-26.1775, -58.1781), zoom 13, tiles de OpenStreetMap con la atribución obligatoria.
  - **Íconos:** con Vite, los marcadores por defecto de Leaflet aparecen rotos porque no encuentra las imágenes. Configurar `L.Icon.Default.mergeOptions({ iconUrl, iconRetinaUrl, shadowUrl })` con `new URL('leaflet/dist/images/marker-icon.png', import.meta.url).href` (y los otros dos).
- **Criterios:** el mapa carga en el celular, los marcadores se ven y no pide ninguna clave de API.
- **Nota:** se puede empezar en el Sprint 2 con los datos de los seeders.

### PAI-72 · Marcadores con dirección, horarios y feria
- **Quién:** Apolo · **HU10** · **Depende de:** PAI-71 y PAI-63 (el popup enlaza al catálogo filtrado por feria)
- **Hacer:** hacer `fetch('/mapa/puntos')` y crear un marcador por punto, con un popup que muestre nombre, dirección, horario, feria y un enlace al catálogo filtrado por esa feria. El popup se arma con `textContent` y nodos del DOM, **no** con HTML concatenado (XSS).

### PAI-79 · Modelo de mensajes y envío (HU12, opcional)
- **Quién:** Lucas · **HU12** · **Depende de:** el MVP probado
- **Hacer:**
  - Migraciones y modelos `Conversacion` y `Mensaje`, con el único (`revendedor_id`, `feriante_id`).
  - **Cuenta del feriante:** hoy ninguna tarea crea un usuario `rol = feriante` ni lo vincula con `feriantes.user_id`, y sin eso el feriante no puede responder. Incluir una acción del admin en el ABM de feriantes, «Crear cuenta», que crea el usuario, lo vincula y le manda el correo para elegir la contraseña (el de recuperación de Breeze).
  - Solo un revendedor inicia una conversación con un feriante que tenga cuenta; el feriante responde.
  - Límite de 10 mensajes por hora por usuario con `RateLimiter`.
- **Antes de empezar:** OpenCode pregunta si el MVP (HU1–HU7 y HU11) ya está mergeado y probado. Si no, no se hace.

### PAI-80 · Policies: solo los participantes leen la conversación
- **Quién:** Mauricio · **HU12** · **Depende de:** PAI-79
- **Hacer:** `ConversacionPolicy` (`view`, `responder`) y `authorize` en el controlador.
- **Pruebas:** un tercero recibe 403.

### PAI-81 · Bandeja de mensajes en las vistas
- **Quién:** Apolo · **HU12** · **Depende de:** PAI-80
- **Hacer:** la bandeja (lista de conversaciones con no leídos), la vista de la conversación y el formulario de respuesta. Al abrirla, marca como leídos los mensajes recibidos.

---

## Sprint 4 · Deseables, pruebas y despliegue (25/11 → 29/11)

### PAI-83 · Listado de usuarios y bloqueo o desbloqueo
- **Quién:** Mauricio · **HU13** · **Depende de:** PAI-43
- **Hacer:**
  - `admin.usuarios.index` con búsqueda y filtro por rol, y las acciones bloquear y desbloquear.
  - Un usuario bloqueado no puede iniciar sesión: en `LoginRequest::authenticate()`, chequear el bloqueo **antes** de `Auth::attempt`, para que no se llegue a crear la sesión, con un mensaje claro.
  - Si ya tenía una sesión abierta, lo saca el middleware `UsuarioNoBloqueado`, **registrado en el grupo `web`** en `bootstrap/app.php` (si no se registra, el bloqueado sigue navegando).
  - No se puede bloquear a uno mismo ni a otro admin.
- **Pruebas:** login bloqueado, expulsión de la sesión y las dos restricciones.

### PAI-84 · Registro de quién bloqueó y cuándo
- **Quién:** Mauricio · **HU13** · **Depende de:** PAI-83
- **Hacer:** guardar `bloqueado_at` y `bloqueado_por` al bloquear, limpiarlos al desbloquear y mostrarlos en el listado.

### PAI-86 · Configurar el servicio de correo
- **Quién:** Mauricio · **HU14** · **Depende de:** nada
- **Hacer:**
  - En desarrollo: `sail artisan sail:add mailpit` (pedir OK, porque cambia `compose.yaml`) y las variables de Mailpit en `.env.example`.
  - En producción: el SMTP del hosting. Va documentado en el manual técnico, **sin credenciales**.
- **Criterios:** los correos de desarrollo se ven en `http://localhost:8025`.

### PAI-87 · Probar el flujo de recuperación de Breeze
- **Quién:** Apolo · **HU14** · **Depende de:** PAI-86 y PAI-41
- **Hacer:** que el correo de recuperación llegue en español (con `lang/es.json` o con una notificación propia), revisar `PasswordResetTest` y probarlo a mano con Mailpit.

### PAI-89 · Registro de consultas por producto
- **Quién:** Lucas · **HU15** · **Depende de:** PAI-64 y PAI-75
- **Hacer:**
  - Migración y modelo `ConsultaProducto`.
  - Registrar con `origen = busqueda` cuando el catálogo se filtra por texto o categoría (un registro por producto distinto en la primera página de resultados, máximo 12), y con `origen = whatsapp` en cada clic de PAI-75.
  - **Deduplicar:** como máximo una consulta por producto, por origen, por visitante (usuario o sesión) y por día. Si no, la tabla crece con cada recarga.
  - `const UPDATED_AT = null;` en el modelo.
- **Antes de empezar:** confirmar con la ERS qué cuenta como «consulta». Esta es una propuesta.

### PAI-90 · Panel de indicadores con filtro de fechas
- **Quién:** Lucas · **HU15** · **Depende de:** PAI-89
- **Hacer:** `admin.estadisticas` con un rango de fechas (por defecto, los últimos 30 días). Muestra los productos más consultados, los clics de WhatsApp por feriante, las ofertas publicadas y los usuarios nuevos. Tablas y barras hechas con Tailwind, sin librería de gráficos. Las consultas tienen que andar en MySQL **y** en SQLite (las pruebas): nada de `DATE_FORMAT`; agrupar con `DATE(created_at)` o en PHP.

### PAI-93 · Pruebas de seguridad: CSRF, XSS, inyección SQL y roles
- **Quién:** Mauricio · **4.1** · **Depende de:** todo lo anterior
- **Hacer:**
  - **Pruebas automáticas:** guardar `<script>alert(1)</script>` como nombre de producto y verificar que el catálogo lo muestra escapado; parámetros de inyección SQL en los filtros; la matriz de roles (PAI-45) actualizada con todas las rutas.
  - **Checklist manual** en `docs/pruebas/seguridad.md`:
    - Un `POST` sin token da 419.
    - Bloqueo por intentos.
    - Las cabeceras de PAI-44 en una respuesta real (DevTools → Network).
    - Que no exista ningún `{!! !!}` (`grep`).
    - Que `APP_DEBUG=false` en producción.

### PAI-105 · Manual técnico (borrador)
- **Quién:** Mauricio · **4.4**
- **Hacer:** OpenCode redacta `docs/manual-tecnico.md` **a partir del código**: arquitectura MVC, modelo de datos, rutas, roles, instalación con Sail y con Herd, variables de entorno y despliegue. El equipo lo revisa y lo completa.

### PAI-106 · README del repositorio
- **Quién:** Lucas · **4.4**
- **Hacer:** actualizar el README con las funcionalidades terminadas, capturas y el enlace al sitio publicado.
