---
description: Implementa una subtarea de Jira según docs/plan-desarrollo.md (ej. /tarea PAI-35)
agent: build
---

Vas a implementar la subtarea de Jira **$1** del proyecto PAIPPA network.

1. Leé `AGENTS.md` y la sección `### $1` de `docs/plan-desarrollo.md`. Si existen `docs/DER.*` o `docs/ERS.*`, leelos también: mandan sobre el plan. Si la sección no existe, frená y preguntá.
2. Ejecutá `git branch --show-current` y `git status`. Si estás en `develop` o en `main`, o hay cambios sin commitear que no son de esta tarea, frená y decime qué hacer (por ejemplo, `git switch develop && git pull && git switch -c feature/$1-descripcion`).
3. Revisá en el código que las dependencias de la tarea estén hechas. Si falta algo, decime qué y frená.
4. Mostrame un **plan corto**: archivos a crear y modificar, qué hace cada uno, las pruebas que vas a escribir y cualquier duda o decisión que tenga que tomar yo. **Esperá mi OK antes de editar.**
5. Implementá solo el alcance de $1, con las convenciones de `AGENTS.md`. Nada de paquetes nuevos sin preguntar.
6. Escribí o actualizá las pruebas de los criterios de aceptación. Ejecutá `./vendor/bin/sail pint --dirty` y `./vendor/bin/sail artisan test` hasta que todo pase. Si una prueba ajena falla, avisame y no la «arregles» cambiándole lo que espera.
7. Cerrá con este resumen en español:
   - **Qué se hizo y por qué**, explicado simple, como para alguien que recién aprende Laravel.
   - **Archivos tocados.**
   - **Cómo probarlo a mano**, paso a paso en el navegador.
   - **Commit propuesto:** `$1: ...`
   - **Texto del pull request**: qué cambia, cómo probarlo y «Jira: $1».
   - **Tres preguntas que podría hacer el tribunal**, con su respuesta corta.
   - **Pendientes o mejoras vistas** que quedaron fuera del alcance.
