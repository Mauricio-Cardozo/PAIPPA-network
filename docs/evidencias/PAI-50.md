
# PAI-50 — Panel del administrador

**Responsable:** Apolo Benítez

## Objetivo
Implementar el panel inicial del administrador de PAIPPA Network, siguiendo el diseño de Figma.

## Implementación
Se reemplazó la pantalla provisoria por una vista Blade con indicadores y una sección de gestión.

### Archivos modificados
- routes/web.php
- resources/views/admin/inicio.blade.php

### Funcionalidades
- Cantidad de feriantes registrados.
- Cantidad de ferias.
- Cantidad de usuarios.
- Ofertas vigentes según fecha y stock disponible.
- Diseño adaptable a dispositivos móviles.

Los indicadores se obtienen mediante consultas Eloquent a la base de datos.

La sección Gestión muestra módulos pendientes de implementación, sin enlaces funcionales todavía.

## Seguridad
El panel conserva los middleware:
- auth
- rol:admin
- sin-cache

## Evidencias
- capturas/PAI-50-panel-admin.png
- capturas/PAI-50-panel-movil.png
- capturas/PAI-50-tests.png

## Ajuste adicional
Se agregó al login un enlace para crear una cuenta, siguiendo el diseño de Figma.

## Estado
Panel implementado y visualizado.
Pendiente: registrar el resultado exacto de las pruebas automáticas y obtener aprobación del Pull Request.
