# PAI-46 — Plantillas y diseño visual

**Responsable:** Apolo Benítez

## Objetivo
Unificar la identidad visual de PAIPPA Network utilizando los colores definidos en Figma.

## Implementación realizada
Se configuraron colores personalizados en Tailwind CSS.

Archivo modificado:
`tailwind.config.js`

Colores incorporados:
- Verde principal: #2F7D32
- Blanco: #FFFFFF

## Justificación técnica
Centralizar los colores permite reutilizarlos en distintas pantallas sin repetir códigos hexadecimales.

## Verificación
Comando ejecutado: `npm.cmd run build`

Resultado: compilación exitosa con Vite 8.3.5.

## Estado
Configuración de colores completada.
Pendiente: aplicar los colores a las plantillas Blade, verificar visualmente y completar el menú según roles.


## Implementación del inicio de sesión

Se adaptó la pantalla de login de Laravel Breeze a la identidad visual de PAIPPA Network, utilizando los colores definidos en Figma.

### Cambios realizados
- Incorporación del nombre PAIPPA Network.
- Traducción de los textos del formulario al español.
- Aplicación del verde institucional (#2F7D32).
- Personalización del botón de ingreso y los campos.
- Conservación de la autenticación original de Laravel.

### Archivos modificados
- resources/views/layouts/guest.blade.php
- resources/views/auth/login.blade.php

### Verificación
Se comprobó visualmente que la pantalla carga correctamente y muestra los cambios realizados.

### Evidencia
Captura: docs/evidencias/capturas/PAI-46-login.png

### Estado
Diseño visual del login completado. Pendiente comprobar el inicio de sesión con un usuario válido.


## Incorporación de identidad visual

Se incorporaron los archivos SVG de PAIPPA Network
para reemplazar la identidad visual predeterminada
de Laravel Breeze.

Archivos:
- public/images/paippa-v1-icono.svg
- public/images/paippa-v1-logo.svg

Se modificó el componente application-logo.blade.php
para utilizar el ícono personalizado.

Objetivo:
Mantener una identidad visual propia y reutilizable
en las pantallas del sistema.

Verificación:
Pendiente de comprobar visualmente en el navegador.


## Preparación del entorno de pruebas

Se ejecutaron las migraciones pendientes mediante
`php artisan migrate`.

Resultado: 7 migraciones ejecutadas correctamente.

Se cargaron datos de ejemplo mediante
`php artisan db:seed`.

Resultado: carga finalizada sin errores.

Objetivo:
Preparar la base de datos local para verificar
la navegación y los menús según el rol del usuario.


## Prueba de seguridad por roles

**Caso:** Acceso de un revendedor al panel administrador.

**Procedimiento:**
1. Iniciar sesión con un usuario de rol revendedor.
2. Intentar acceder a la ruta /admin.

**Resultado esperado:**
El sistema debe impedir el acceso y mostrar HTTP 403.

**Resultado obtenido:**
Se mostró correctamente la página 403 con el mensaje
"No tenés permiso para entrar acá".

**Estado:** APROBADO.

**Evidencia:**
docs/evidencias/capturas/PAI-46-acceso-denegado.png

**Conclusión:**
Se verificó que el middleware de roles impide el acceso
a una ruta administrativa desde un usuario sin permisos.


## Prueba de navegación móvil

**Objetivo:** Comprobar que el menú de PAIPPA Network
se adapte correctamente a dispositivos móviles.

**Procedimiento:**
1. Iniciar sesión como revendedor.
2. Activar la vista móvil del navegador.
3. Desplegar el menú de navegación.
4. Verificar las opciones disponibles.

**Resultado esperado:**
Mostrar Catálogo, Mi perfil y Cerrar sesión,
sin incluir Administración.

**Resultado obtenido:**
El menú se desplegó correctamente y mostró
las opciones correspondientes al revendedor.

**Estado:** APROBADO.

**Evidencia:**
docs/evidencias/capturas/PAI-46-movil.png


## Evidencias visuales de PAI-46

| Evidencia | Archivo | Resultado |
|---|---|---|
| Inicio de sesión | capturas/01-login.png | Diseño PAIPPA aplicado |
| Menú administrador | capturas/02-menu-admin.png | Opciones administrativas visibles |
| Menú revendedor | capturas/03-menu-revendedor.png | Administración oculta |
| Acceso restringido | capturas/04-acceso-denegado.png | HTTP 403 correcto |
| Menú móvil | capturas/05-menu-movil.png | Navegación adaptable |

Las capturas corresponden a verificaciones manuales
realizadas en el entorno local de desarrollo.


## Pruebas automáticas

Se ejecutó la suite automatizada de Laravel
mediante el comando `php artisan test`.

### Problema detectado
En la primera ejecución fallaron 41 pruebas porque
PHP no tenía habilitado el controlador SQLite.

### Solución aplicada
Se habilitaron las extensiones `pdo_sqlite`
y `sqlite3` en la configuración local de PHP.

### Resultado final
- Pruebas aprobadas: 43
- Pruebas fallidas: 0
- Aserciones: 108
- Duración: 13,34 segundos

**Estado:** APROBADO.

### Evidencia
docs/evidencias/capturas/06-tests-aprobados.png

### Conclusión
Se resolvió un problema de configuración del
entorno y se ejecutaron correctamente las
pruebas automatizadas del proyecto.

Este resultado corresponde a la suite existente;
no representa una medición de cobertura del código.
