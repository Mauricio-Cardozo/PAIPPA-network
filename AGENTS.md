# AGENTS.md · PAIPPA network

Instrucciones para agentes de código (OpenCode u otros) que trabajen en este repositorio.
El plan tarea por tarea está en `docs/plan-desarrollo.md`.

## El proyecto

Plataforma web que conecta a los feriantes del PAIPPA con revendedores de Formosa Capital.
Es el Trabajo Final Integrador del Grupo 14 (UTN FRRe, TUP 2026). El equipo es Lucas, Apolo y Mauricio.

- **Defensa individual:** cada integrante defiende **todo** el sistema ante un tribunal. El código tiene que ser simple, legible y explicable. Antes que lo ingenioso, va lo que se entiende.
- **Roles del sistema:** `admin` (carga y valida todo), `revendedor` (navega el catálogo y contacta) y `feriante` (cuenta opcional, para la mensajería). Los visitantes sin sesión solo ven el catálogo, los perfiles y el mapa.
- **Prioridad de las historias:** MVP obligatorio HU1–HU7 y HU11 · útiles HU8, HU9, HU10 y HU13 · deseables HU14 y HU15 · opcional HU12 (solo si el MVP está probado).

## Fuente de verdad, en este orden

1. Lo que diga la persona en la conversación.
2. `docs/DER.*`, `docs/diccionario.*` y `docs/ERS.*`, cuando existan.
3. `docs/plan-desarrollo.md`.
4. Las convenciones de este archivo.

Si dos fuentes se contradicen, o falta un dato, **preguntá**. No inventes columnas, roles ni reglas de negocio.

## Stack (no cambiar versiones)

- **Backend:** Laravel 13 y PHP `^8.3`. Se desarrolla en 8.5, pero el código tiene que andar en 8.3: no uses funciones exclusivas de PHP 8.4 u 8.5.
- **Base de datos:** MySQL 8.4 en desarrollo y SQLite en memoria en las pruebas (`phpunit.xml`).
- **Frontend:** Breeze (Blade), Tailwind CSS **3**, Alpine.js y Vite. Sin React, sin Vue y sin Livewire.
- **Pruebas:** PHPUnit 12 (no Pest).
- **Entorno:** Laravel Sail (Docker en WSL2).

## Comandos

El equipo usa Sail. Si la persona dice que usa Herd, sacá el prefijo `./vendor/bin/sail`.

```bash
./vendor/bin/sail up -d                      # levantar
./vendor/bin/sail artisan test               # todas las pruebas
./vendor/bin/sail artisan test --filter=Nombre
./vendor/bin/sail pint --dirty               # formato del código tocado
./vendor/bin/sail artisan migrate            # migraciones nuevas
./vendor/bin/sail artisan migrate:fresh --seed   # borra la base local: pedir permiso antes
./vendor/bin/sail npm run build              # compilar el frontend
./vendor/bin/sail artisan route:list
```

## Git: lo que podés y lo que no

- **Ramas:** se sigue el modelo de Git Flow (`main`, `develop`, `feature/`, `release/`, `hotfix/`), pero **sin** la herramienta `git-flow`, que no está instalada. Trabajás **solo** en una rama `feature/PAI-xx-descripcion` que sale de `develop`. Si estás en `develop` o en `main`, frená y pedí crearla con `git switch develop && git pull && git switch -c feature/PAI-xx-descripcion`.
- **Commits:** chicos, en español, en presente y con la clave de Jira: `PAI-35: agrega el rol al usuario`.
- **Prohibido:** `git push`, `merge`, `rebase`, `reset --hard` y force. El push y el pull request los hace la persona.
- **Al terminar:** proponé el mensaje de commit y el texto del pull request.

## Dependencias

- **Nunca** ejecutes `composer update` ni `npm update`, ni edites `composer.lock` o `package-lock.json` a mano.
- **Paquetes nuevos:** no se agregan sin aprobación explícita de la persona en la conversación.
  - Pre-aprobado: `leaflet` (npm), solo para PAI-71.
  - Si se aprueba otro: `composer require paquete` o `npm install paquete`, y el lock va en el mismo commit.
- Para lo que Laravel ya trae (validación, rate limiting, policies, notificaciones, `Prunable`), no se agregan paquetes.

## Secretos

- No leas, no edites y no subas `.env`. Las variables nuevas van a `.env.example` con valores de ejemplo.
- No escribas contraseñas, claves ni teléfonos reales en el código, en los seeders ni en las pruebas.

## Convenciones de código

- **Idioma:** el dominio va en español (modelos, tablas, columnas, rutas y vistas): `Feriante`, `PuntoRetiro`, `ofertas.precio_caja`. Los métodos propios del framework quedan como son (`index`, `store`, `update`). Comentarios y textos de la interfaz en español rioplatense («Iniciá sesión»).
- **Modelos:**
  - Declarar siempre `protected $table`, porque Laravel pluraliza en inglés.
  - Usar los atributos PHP de Laravel 13 como en `User`: `#[Fillable([...])]` y `#[Hidden([...])]`, y `casts()` como método.
  - Cada relación con su tipo de retorno.
  - Borrado lógico (`SoftDeletes`) en las entidades que tienen ABM.
  - **Fillable:** solo los datos que el usuario edita en un formulario. `rol`, `bloqueado_at`, `bloqueado_por` y `validado_at` **no** son fillable: en el código de la aplicación se asignan a mano (`$user->rol = Rol::Admin;` o `forceFill`). Las factories crean los modelos sin protección (`Model::unguarded`), así que en factories y pruebas sí se pueden pasar directo (`User::factory()->create(['rol' => Rol::Admin])`).
  - Las tablas que solo tienen `created_at` (registros de clics y consultas) declaran `const UPDATED_AT = null;`.
- **Enums:** en `app/Enums` (por ejemplo `Rol`), con valores `string` y un método `etiqueta()` para mostrarlos.
- **Migraciones:**
  - Nunca editar una migración que ya está en `develop`: se crea una nueva.
  - Claves foráneas con `constrained()` y el `onDelete` que dice la tabla de políticas de `docs/plan-desarrollo.md`. Nunca implícito.
  - **Borrado lógico y claves foráneas:** el soft delete es un `UPDATE`, así que el `restrict` de la base **no** lo frena. «No se puede dar de baja X si tiene Y» se chequea en el controlador (`$categoria->productos()->exists()`) y se prueba.
  - **Únicos con borrado lógico:** un `unique('nombre')` impide reusar el nombre de un registro dado de baja. Seguir lo que diga el plan para cada tabla.
  - Dinero en `decimal(10,2)`. Latitud y longitud en `decimal(10,7)`.
- **Fechas:**
  - Zona horaria `America/Argentina/Cordoba` (`APP_TIMEZONE`). «Hoy» es `today()` en esa zona.
  - Las columnas `date` se comparan con `where('vigente_hasta', '>=', today()->toDateString())`, no con `whereDate()`, para que el índice se use.
  - En las pruebas que dependen de la fecha, congelar el tiempo con `$this->travelTo(...)`.
- **Pruebas:**
  - PHPUnit 12 **no** lee anotaciones en docblocks: usar atributos (`#[Test]`, `#[DataProvider('casos')]` con método `public static`).
  - Las pruebas corren en SQLite: nada de funciones exclusivas de MySQL (`DATE_FORMAT`, `IF()`, etc.). Si hace falta agrupar por fecha, `DATE(...)` o agrupar en PHP.
- **Validación:** siempre con Form Requests (`app/Http/Requests`), con mensajes y atributos en español.
- **Controladores:** finos. Los del panel van en `App\Http\Controllers\Admin`, con `Route::resource` donde aplique y las rutas con nombre (`admin.ofertas.index`).
- **Autorización:** el middleware `rol:admin` protege el panel y las Policies protegen los registros de un usuario (por ejemplo, las conversaciones).
- **Consultas:**
  - Eloquent con eager loading (`with`) y sin N+1.
  - Scopes en los modelos (`Oferta::publicadas()`).
  - Cero SQL concatenado: si hace falta SQL crudo, con bindings.
- **Vistas:**
  - Siempre `{{ }}`. Nunca `{!! !!}` con datos de usuarios.
  - Reutilizar los componentes de `resources/views/components`.
  - Mobile-first, que funcione en 320, 375 y 768 px y en escritorio.
- **JavaScript:** en `resources/js`, importado por Vite. Nada de `innerHTML` con datos de la base: usar `textContent`.
- **Breeze:** varios controladores y pruebas de Breeze redirigen a la ruta `dashboard`. No se borra: queda como un redirector según el rol.

## Seguridad (revisar en cada tarea)

- `rol` **no** es asignable en masa: se setea a mano en el código.
- `@csrf` en todos los formularios `POST`, `PUT`, `PATCH` y `DELETE`.
- Rate limiting en el login (ya viene en Breeze), en el registro y en el envío de mensajes.
- Cabeceras de seguridad en todas las respuestas (middleware de PAI-44). Si se agrega una CSP, tiene que permitir los tiles de OpenStreetMap y Vite, o se rompe el mapa.
- Datos personales (Ley 25.326): guardar solo lo necesario. Los clics de WhatsApp no guardan el contenido del mensaje y se borran a los 12 meses.

## Cómo trabajar cada tarea

1. Leer la sección `### PAI-xx` de `docs/plan-desarrollo.md` y los archivos que nombra.
2. Revisar que sus dependencias estén hechas en el código. Si no, avisar.
3. **Mostrar un plan corto** (archivos a crear o tocar y pruebas) y **esperar el OK**.
4. Implementar **solo** el alcance de esa tarea. Si ves otra cosa para mejorar, anotala al final y no la hagas.
5. Escribir o actualizar las pruebas de los criterios de aceptación.
6. Ejecutar `sail pint --dirty` y `sail artisan test`. Todo tiene que pasar.
7. Cerrar con un resumen en español:
   - Qué se hizo y por qué, en lenguaje simple.
   - Archivos tocados.
   - Cómo probarlo a mano en el navegador.
   - Mensaje de commit y texto del pull request, con «Jira: PAI-xx».
   - Tres preguntas que el tribunal podría hacer sobre este código, con su respuesta corta.
