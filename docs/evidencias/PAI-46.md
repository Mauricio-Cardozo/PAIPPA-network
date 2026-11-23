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
