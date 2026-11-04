---
description: Explica una tarea o un archivo para preparar la defensa (ej. /explicar PAI-43 o /explicar app/Models/Oferta.php)
agent: plan
---

Quiero entender **$ARGUMENTS** para poder defenderlo ante el tribunal. **No edites nada.**

1. Si es una clave PAI-xx, buscá los archivos con `git log --all --oneline --grep="$1"` y la sección del plan en `docs/plan-desarrollo.md`. Si es una ruta, leé ese archivo.
2. Explicame en español y simple:
   - **Para qué existe**, qué problema del usuario resuelve y con qué HU se relaciona.
   - **Cómo funciona**, recorriendo el flujo real: ruta → middleware → controlador → modelo → vista, con fragmentos cortos del código del repo.
   - **Por qué se hizo así** y qué alternativa se descartó.
   - **Qué conceptos de Laravel usa** (Eloquent, middleware, Form Request, Policy, Blade…), con una definición de una línea.
3. Después tomame **5 preguntas**, de a una, como lo haría el tribunal. Esperá mi respuesta y corregime antes de pasar a la siguiente.
