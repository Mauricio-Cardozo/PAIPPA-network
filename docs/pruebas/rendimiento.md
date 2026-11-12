# Prueba de rendimiento del catálogo (PAI-65)

**Objetivo (RNF-007):** el catálogo filtrado responde en menos de 3 segundos con 2000 ofertas.

## Cómo se mide

```bash
./vendor/bin/sail artisan migrate:fresh --seed          # base de prueba (borra la local)
./vendor/bin/sail artisan db:seed --class=VolumenSeeder # +2000 ofertas
```

Después, abrir el catálogo con cada filtro y anotar el tiempo de la pestaña **Network** de las DevTools (columna *Time* del documento).

## Resultados en SQLite (entorno de desarrollo, 12/11/2026)

2040 ofertas en total, 602 publicadas.

| Consulta | Antes de los índices | Después de los índices | Respuesta HTTP completa |
|---|---|---|---|
| Sin filtros | 5,1 ms | 4,9 ms | 0,39 s (primera carga, incluye compilar vistas) |
| Texto (`?q=tom`) | 4,7 ms | 4,6 ms | 0,034 s |
| Categoría + precio | 3,8 ms | 4,6 ms | 0,033 s |
| Feria (página 3) | 4,3 ms | 4,6 ms | 0,030 s |
| `/mapa/puntos` (JSON) | — | — | 0,029 s |

Los tiempos de consulta son el promedio de 20 ejecuciones de `Oferta::publicadas()->filtrar(...)->paginate(12)` con el eager loading del controlador.

**Plan de la consulta (`EXPLAIN QUERY PLAN`):**

- Antes: `SCAN ofertas` (recorre toda la tabla) y `USE TEMP B-TREE FOR ORDER BY`.
- Después: `SEARCH ofertas USING INDEX ofertas_vigencia_stock_index (vigente_hasta>?)`: usa el índice de vigencia y stock.

Con 2000 ofertas la diferencia de tiempo es mínima porque la tabla es chica, pero el plan pasa de recorrer toda la tabla a buscar por índice, que es lo que importa cuando la tabla crece.

## Índices agregados

Migración `2026_11_20_000001_add_indices_catalogo_to_ofertas_table`:

- `ofertas (vigente_hasta, vigente_desde, cajas_disponibles)`: el filtro que se aplica siempre (scope `vigentes`).
- `ofertas (updated_at)`: el orden del listado.

Las claves foráneas ya tienen índice propio y `feriantes.validado_at` también. Un índice común en `productos.nombre` **no** ayuda a `LIKE '%texto%'`, así que no se agregó: con este volumen la búsqueda por texto tarda menos de 5 ms.

## Pendiente: medir en MySQL 8.4 (con Sail)

En el entorno de desarrollo no había MySQL disponible. Completar esta tabla con Sail:

```bash
./vendor/bin/sail artisan tinker
>>> DB::select('EXPLAIN '.App\Models\Oferta::publicadas()->filtrar(['categoria' => 2, 'precio_max' => 15000])->latest('updated_at')->limit(12)->toRawSql());
```

| Consulta | Tiempo en Network | `key` usado en el EXPLAIN |
|---|---|---|
| Sin filtros | | |
| Texto | | |
| Categoría + precio | | |
| Feria | | |

**Conclusión:** se cumple el objetivo con amplio margen (menos de 0,4 s contra 3 s).
