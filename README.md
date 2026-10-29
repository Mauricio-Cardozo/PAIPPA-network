# PAIPPA network

Plataforma web que conecta a los feriantes del PAIPPA con revendedores de Formosa Capital.
Trabajo Final Integrador · Tecnicatura Universitaria en Programación · UTN FRRe · 2026.

**Equipo:** Lucas (coordinación y lógica de negocio) · Apolo (frontend y QA) · Mauricio (arquitectura, base de datos y seguridad)

## Stack

Laravel 13 · PHP 8.3 o superior (desarrollo en 8.5) · MySQL 8.4 · Breeze (Blade) · Tailwind · Vite

## Estado

Proyecto base creado con Laravel 13, Breeze (Blade), Sail y CI en PHP 8.3 y 8.5.

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
- `feature/PAI-xx-nombre`: una por HU o subtarea, sale de `develop` y vuelve a `develop`.
- `release/v0.x`: al cierre de cada sprint.
- `hotfix/v1.0.x`: solo para errores en lo publicado.

Configuración inicial de Git Flow:

```bash
git flow init      # Enter en todo y, en "Version tag prefix", escribir: v
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
