# PAIPPA network

[![Pruebas](https://github.com/Mauricio-Cardozo/PAIPPA-network/actions/workflows/tests.yml/badge.svg?branch=develop)](https://github.com/Mauricio-Cardozo/PAIPPA-network/actions/workflows/tests.yml)

Plataforma web que conecta a los feriantes del PAIPPA (Instituto Provincial de Acción Integral para el Pequeño Productor Agropecuario) con los compradores y revendedores de Formosa Capital.
Trabajo Final Integrador · Tecnicatura Universitaria en Programación · UTN FRRe, Sede Formosa · 2026.

**Equipo:** Lucas (coordinación y lógica de negocio) · Apolo (frontend y QA) · Mauricio (arquitectura, base de datos y seguridad)

## Objetivo

Hoy la oferta de cada feriante llega solo a quien va a la feria o sigue sus estados de WhatsApp. PAIPPA network junta en un catálogo público, pensado para el celular, las ofertas vigentes de las ferias del PAIPPA: qué hay, a qué precio, en qué cantidad y dónde se retira. El comprador contacta al feriante por WhatsApp con un mensaje ya armado, y el PAIPPA administra todo desde un panel.

## Qué hace

- **Catálogo público** con filtros por texto, categoría, feria y precio máximo, paginado de a 12.
- **Cómo comprar:** los tres pasos, los días y horarios de las ferias y el contacto del PAIPPA, arriba del catálogo.
- **Perfil del feriante** con sus ofertas publicadas y sus puntos de retiro.
- **Mapa** de los puntos de retiro con Leaflet y OpenStreetMap.
- **Contacto por WhatsApp** con un mensaje prearmado. Si el feriante no autoriza mostrar su número, el contacto va al área de Comercialización del PAIPPA.
- **Panel del administrador:** ferias (fijas e itinerantes), puntos de retiro, categorías, productos, feriantes (con validación), ofertas (con actualización rápida de cantidad y cierre con cantidad vendida) y usuarios (con bloqueo).
- **Estadísticas:** productos más consultados, contactos por WhatsApp por feriante, ofertas publicadas y usuarios nuevos, con filtro de fechas.

Reglas principales: solo se publican las ofertas de feriantes validados por el PAIPPA, una oferta vale como máximo 7 días, las bajas son lógicas y los clics de WhatsApp se guardan sin el mensaje y se borran a los 12 meses (Ley 25.326).

## Roles

| Rol | Qué puede hacer |
|---|---|
| Visitante | Ver el catálogo, los perfiles de los feriantes y el mapa |
| Comprador | Lo mismo, más contactar a los feriantes por WhatsApp (necesita cuenta) |
| Feriante | Cuenta opcional, pensada para la mensajería interna (a futuro) |
| Administrador (PAIPPA) | Cargar y validar ferias, feriantes, productos, ofertas y usuarios, y ver las estadísticas |

## Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | Laravel 13 · PHP 8.3 o superior (desarrollo en 8.5) · MVC |
| Acceso | Laravel Breeze (Blade) y middleware de roles |
| Frontend | Blade · Tailwind CSS 3 · Alpine.js · Vite |
| Base de datos | MySQL 8.4 con Eloquent (las pruebas usan SQLite en memoria) |
| Mapa | Leaflet + OpenStreetMap |
| Calidad | PHPUnit 12 · Laravel Pint · GitHub Actions |
| Gestión | Jira · GitHub con Git Flow y pull requests revisados |

## Usuarios de prueba

El seeder los crea solo en el entorno local (`APP_ENV=local`), nunca en producción. La contraseña de los dos es `password`.

| Correo | Rol |
|---|---|
| `admin@paippa.test` | Administrador |
| `test@example.com` | Comprador |

## Requisitos

- Git
- Docker Desktop con WSL2 (recomendado), o Laravel Herd + MySQL + Node.js
- Una cuenta de GitHub con acceso al repositorio

## Instalación con Docker (Sail)

Se trabaja dentro de WSL, en `~/`, nunca en `C:\` ni en `/mnt/c`.

```bash
git clone https://github.com/Mauricio-Cardozo/PAIPPA-network.git
cd PAIPPA-network
git checkout develop
cp .env.example .env

# composer todavía no existe en el host: se instala dentro de un contenedor
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
  laravelsail/php84-composer:latest composer install --ignore-platform-reqs

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm ci
./vendor/bin/sail npm run dev
```

La aplicación queda en <http://localhost:8080> y Vite en el puerto 5173.
La primera vez, `sail up -d` compila la imagen de PHP 8.5 y puede tardar varios minutos.

Si en Linux/WSL tu usuario no es 1000, ajustá en `.env`:

```bash
WWWUSER=$(id -u)
WWWGROUP=$(id -g)
```

## Instalación con Herd

```bash
git clone https://github.com/Mauricio-Cardozo/PAIPPA-network.git
cd PAIPPA-network
git checkout develop
composer install
npm ci
cp .env.example .env
```

En el `.env`, cambiar la conexión para usar el MySQL local:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=paippa
DB_USERNAME=root
DB_PASSWORD=
```

Crear la base `paippa` con cotejamiento `utf8mb4_unicode_ci` y después:

```bash
php artisan key:generate
php artisan migrate --seed
composer run dev
```

La aplicación queda en la URL que muestre Herd o `composer run dev`.

## Variables de entorno propias

| Variable | Para qué sirve |
|---|---|
| `APP_TIMEZONE` | Zona horaria del sistema: `America/Argentina/Cordoba`. «Hoy» se calcula en esta zona |
| `PAIPPA_WHATSAPP_COMERCIALIZACION` | WhatsApp del área de Comercialización del PAIPPA (549 + área + número, sin 0 ni 15). Se usa cuando el feriante no autoriza mostrar su número |

Con Sail, los correos (por ejemplo, el de recuperar la contraseña) no salen a internet: quedan en Mailpit, en <http://localhost:8025>.

## Tareas programadas

`model:prune` corre todos los días y borra los clics de WhatsApp de más de 12 meses. En el servidor hace falta el cron de Laravel:

```bash
* * * * * cd /ruta/al/proyecto && php artisan schedule:run >> /dev/null 2>&1
```

## Base de datos

- Motor: MySQL 8.4.
- Base: `paippa`.
- Cotejamiento: `utf8mb4_unicode_ci`.
- Con Sail, el contenedor crea la base y el usuario `sail` automáticamente.
- El puerto `3307` del host está mapeado al `3306` del contenedor para no chocar con un MySQL local. Se cambia con `FORWARD_DB_PORT`.

## Pruebas

```bash
# Con Sail
./vendor/bin/sail artisan test

# Con Herd
php artisan test
```

Las pruebas usan SQLite en memoria y no necesitan MySQL. El workflow de GitHub Actions las corre en PHP 8.3 y 8.5.

## Reglas de dependencias

- Para instalar o probar: `composer install` y `npm ci`. **Nunca** `composer update` ni `npm install`, porque reescriben los lock.
- Para agregar o actualizar un paquete: lo hace una sola persona, en su propia rama, y sube `composer.json` y `composer.lock` juntos en el mismo PR.
- Si dos PRs chocan en `composer.lock`: no se arregla a mano. Se toma el lock de `develop` y se vuelve a ejecutar `composer require paquete`.

## Ramas (Git Flow)

- `main`: solo versiones entregadas, con tag (`v0.1`, `v0.2`, `v0.3`, `v1.0`).
- `develop`: integración. Todo entra por pull request con la aprobación de otro integrante.
- `feature/PAI-xx-nombre`: una por HU o tarea, sale de `develop` y vuelve a `develop`.
- `release/v0.x`: al cierre de cada sprint.
- `hotfix/v1.0.x`: solo para errores en lo publicado.

Se sigue el modelo de ramas de Git Flow con comandos de git comunes (no hace falta instalar `git-flow`). El merge siempre se hace por pull request en GitHub.

```bash
# nueva feature
git switch develop && git pull
git switch -c feature/PAI-xx-descripcion
git push -u origin feature/PAI-xx-descripcion     # y abrir el PR a develop

# cierre de sprint
git switch develop && git pull
git switch -c release/v0.1
git push -u origin release/v0.1                   # PR a main; después del merge:
git switch main && git pull && git tag v0.1 && git push origin v0.1
# y un PR de main a develop si en la release hubo ajustes
```

## Commits

Mensajes cortos con la clave de Jira: `PAI-12: valida feriante antes de publicar`.

## Problemas frecuentes

| Problema | Solución |
|---|---|
| `composer install` dice que falta una extensión de PHP | Vía Herd: activarla en Herd → PHP → Extensions. Ejecutar `composer check-platform-reqs` para ver cuál falta |
| `Your lock file does not contain a compatible set of packages` | Alguien ejecutó `composer update` en otra versión. Descartar los cambios con `git checkout composer.lock` y volver a `composer install` |
| `SQLSTATE[HY000] [2002]` al migrar | MySQL no está corriendo, o el `.env` tiene `DB_HOST=mysql` en Herd (va `127.0.0.1`) o `127.0.0.1` en Sail (va `mysql`) |
| La página sale sin estilos o con `Vite manifest not found` | Falta `npm run dev` o `npm run build` |
| Sail va muy lento | El proyecto está en `/mnt/c/...`. Moverlo a `~/` dentro de Ubuntu |
| El puerto 8080 o 3307 está ocupado | Cambiar `APP_PORT` o `FORWARD_DB_PORT` en `.env` y volver a ejecutar `sail up -d` |
| GitHub rechaza el push a `develop` | Es la protección funcionando: crear una feature y abrir un PR |

## Documentación

- Plan de desarrollo tarea por tarea: [`docs/plan-desarrollo.md`](docs/plan-desarrollo.md).
- Pruebas de seguridad y de rendimiento: [`docs/pruebas`](docs/pruebas).
- Evidencias de pruebas manuales: [`docs/evidencias`](docs/evidencias).
- Requerimientos (ERS), diseño (arquitectura, DER y diccionario de datos) y manuales: en la carpeta del grupo entregada a la cátedra.
