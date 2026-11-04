---
description: Revisa los cambios de la rama actual contra develop, sin editar nada
agent: plan
---

Revisá los cambios de la rama actual con `git diff develop...HEAD` y `git status`, contra las reglas de `AGENTS.md`. **No edites ningún archivo.**

Revisá y reportá, de lo más grave a lo menos grave:
1. **Seguridad:** `rol` asignable en masa, rutas sin el middleware que corresponde, `{!! !!}` o `innerHTML` con datos, formularios sin `@csrf`, SQL concatenado, validación faltante, datos personales de más, secretos o `.env` en el diff.
2. **Correctitud:** que se cumplan los criterios de la sección de `docs/plan-desarrollo.md` de esta tarea (sacá la clave PAI-xx del nombre de la rama).
3. **Base de datos:** migraciones ya mergeadas que se editaron, claves foráneas sin `onDelete`, modelos sin `$table`, consultas N+1.
4. **Pruebas:** criterios sin prueba y pruebas que no prueban nada.
5. **Alcance:** cambios que no son de esta tarea, y cambios en `composer.lock` o `package-lock.json` sin un paquete aprobado.
6. **Estilo:** nombres en el idioma equivocado, textos de la interfaz sin traducir, código difícil de explicar en la defensa.

Para cada punto: archivo y línea, el problema y cómo arreglarlo. Si no encontrás nada importante, decilo.
