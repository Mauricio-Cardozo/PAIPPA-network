# Rendimiento del catálogo (PAI-65)

**Requisito (RNF-007):** los listados del catálogo responden en menos de 3 segundos.
**Resultado:** se cumple con margen. Con 2000 ofertas, el peor caso tarda menos de 0,1 s; con 20.000, menos de 0,6 s. Agregar índices **no** mejoró los tiempos, así que no se agregaron (ver «Índices»).

Medido el 26/11/2026.

## Cómo se midió

- **Base:** MySQL 8.4.13, el motor del proyecto, en un contenedor temporal que se borró al terminar. No se usó la base de desarrollo.
- **PHP:** 8.4, en la misma PC.
- **Datos:** `migrate:fresh --seed` más `db:seed --class=VolumenSeeder`. El `VolumenSeeder` no corre por defecto: agrega 50 feriantes y 2000 ofertas con fechas y cantidades variadas, para que haya ofertas publicadas, vencidas, futuras y sin stock, como en la realidad.
- **Qué se midió:** la página completa del catálogo (`GET /`), procesada por Laravel de punta a punta (rutas, controlador, consultas y vista), 5 veces por caso. Se informa la mediana y el peor tiempo. No incluye la red ni el navegador.
- Además, el `EXPLAIN` de la consulta real: `Oferta::publicadas()->filtrar(...)->latest('updated_at')`.

## Resultados

### 2000 ofertas (701 publicadas): el volumen de la prueba

| Caso | Mediana | Peor |
|---|---|---|
| Sin filtros | 77 ms | 2917 ms* |
| Texto (`q=man`) | 27 ms | 130 ms |
| Categoría | 67 ms | 76 ms |
| Feria | 86 ms | 361 ms |
| Combinado (texto, categoría, feria y precio máximo) | 39 ms | 46 ms |

\* La primera petición compila las vistas Blade (en frío). Desde la segunda, menos de 0,1 s.

### 20.000 ofertas (6771 publicadas): 10 veces más, para ver el margen

| Caso | Mediana | Peor |
|---|---|---|
| Sin filtros | 457 ms | 510 ms |
| Texto | 41 ms | 46 ms |
| Categoría | 456 ms | 474 ms |
| Feria | 442 ms | 461 ms |
| Combinado | 101 ms | 106 ms |

## EXPLAIN

Con 2000 ofertas y con todos los filtros:

| Tabla | Tipo | Índice usado | Filas estimadas |
|---|---|---|---|
| puntos_retiro | ref | `puntos_retiro_feria_id_foreign` | 2 |
| productos | ref | `productos_categoria_id_foreign` | 7 |
| ofertas | ref | `ofertas_producto_id_foreign` | 226 |
| feriantes | eq_ref | PRIMARY | 1 |

MySQL arranca por las tablas chicas (puntos de retiro y productos) y llega a las ofertas por el índice de la clave foránea `producto_id`, que Laravel crea con `constrained()`. Sin filtros, recorre los 6 puntos de retiro y los 16 productos completos, porque son tablas muy chicas, y ordena por `updated_at` en memoria («Using filesort»).

## Índices

Se probaron los índices que proponía el plan, en `ofertas (vigente_hasta, disponible_desde)` y en `ofertas (updated_at)`, con 2000 y con 20.000 ofertas:

| Volumen | Sin índices | Con índices |
|---|---|---|
| 2000 ofertas, sin filtros (solo la consulta) | 25 ms | 25 ms |
| 20.000 ofertas, sin filtros (página completa) | 457 ms | 454 ms |

MySQL no los usa: el plan sigue empezando por los puntos de retiro y los productos. Como un índice que no se usa solo hace más lentas las altas y los cambios de ofertas, **no se agregaron**. Las claves foráneas ya tienen índice, y `feriantes.validado_at` tiene el suyo desde la migración original.

**Búsqueda por texto:** `LIKE '%texto%'` no puede usar un índice común, pero como filtra primero los productos (16 filas) es el caso más rápido. No hace falta `FULLTEXT`.

**Cuándo revisarlo:** si el catálogo llegara a decenas de miles de ofertas publicadas, conviene repetir esta medición antes de agregar índices.

## Cómo repetir la medición

```bash
./vendor/bin/sail artisan migrate:fresh --seed     # borra la base local
./vendor/bin/sail artisan db:seed --class=VolumenSeeder
```

Después, abrir el catálogo con la pestaña Network de DevTools y mirar el tiempo de `/` con y sin filtros.
