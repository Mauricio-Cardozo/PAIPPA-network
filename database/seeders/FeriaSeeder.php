<?php

namespace Database\Seeders;

use App\Enums\TipoFeria;
use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Illuminate\Database\Seeder;

/**
 * Ferias del relevamiento con el PAIPPA (Relevamiento_Grupo14, PAIPPA 2 y 3;
 * ERS RE-04): tres ferias fijas los viernes y una itinerante de ejemplo.
 *
 * Datos a confirmar con el PAIPPA:
 * - El horario: el relevamiento solo informa el día (viernes). Queda vacío y el
 *   admin lo completa desde el panel.
 * - Las coordenadas son APROXIMADAS, para que el mapa muestre los puntos.
 * - La feria itinerante es de ejemplo: el relevamiento dice que hay ferias
 *   itinerantes en distintos barrios, pero no cuáles.
 *
 * Los feriantes y las ofertas son de ejemplo, con nombres y teléfonos ficticios.
 */
class FeriaSeeder extends Seeder
{
    public function run(): void
    {
        $ferias = [
            [
                'nombre' => 'Feria del B° La Paz',
                'tipo' => TipoFeria::Fija,
                'direccion' => 'B° La Paz', 'barrio' => 'La Paz', 'dias' => 'Viernes',
                'puntos' => [
                    ['Feria del B° La Paz', 'B° La Paz', 'Viernes (horario a confirmar)', -26.1985, -58.2085],
                ],
            ],
            [
                'nombre' => 'Feria del playón del PAIPPA',
                'tipo' => TipoFeria::Fija,
                'direccion' => 'Padre Grotti 1040', 'barrio' => 'Centro', 'dias' => 'Viernes',
                'puntos' => [
                    ['Playón del PAIPPA', 'Padre Grotti 1040', 'Viernes (horario a confirmar)', -26.1932, -58.1882],
                ],
            ],
            [
                'nombre' => 'Feria del B° La Nueva Formosa',
                'tipo' => TipoFeria::Fija,
                'direccion' => 'Centro Comunitario del B° La Nueva Formosa', 'barrio' => 'La Nueva Formosa', 'dias' => 'Viernes',
                'puntos' => [
                    ['Centro Comunitario La Nueva Formosa', 'Centro Comunitario del B° La Nueva Formosa', 'Viernes (horario a confirmar)', -26.2058, -58.2271],
                ],
            ],
            [
                'nombre' => 'Feria itinerante (ejemplo)',
                'tipo' => TipoFeria::Itinerante,
                'direccion' => 'Plaza del B° Guadalupe', 'barrio' => 'Guadalupe', 'fecha' => today()->next('Wednesday')->toDateString(),
                'puntos' => [
                    ['Plaza del B° Guadalupe', 'Plaza del B° Guadalupe', 'Fecha y horario a confirmar', -26.1700, -58.1880],
                ],
            ],
        ];

        $productos = Producto::all();

        foreach ($ferias as $i => $datos) {
            $feria = Feria::create(collect($datos)->except('puntos')->all());

            $puntos = collect($datos['puntos'])->map(fn ($p) => PuntoRetiro::create([
                'feria_id' => $feria->id,
                'nombre' => $p[0],
                'direccion' => $p[1],
                'horario' => $p[2],
                'latitud' => $p[3],
                'longitud' => $p[4],
            ]));

            // 10 feriantes en las ferias fijas (4 + 3 + 3); los dos últimos sin
            // validar. La itinerante queda sin feriantes: cada feriante tiene una
            // sola feria (feriantes.feria_id).
            $cantidad = match ($i) {
                0 => 4,
                1, 2 => 3,
                default => 0,
            };

            for ($n = 1; $n <= $cantidad; $n++) {
                $numero = Feriante::count() + 1;

                $feriante = Feriante::factory()->create([
                    'feria_id' => $feria->id,
                    'nombre' => 'Feriante de ejemplo '.$numero,
                    'telefono_whatsapp' => '549370000'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT),
                    'validado_at' => $numero <= 8 ? now() : null,
                ]);

                // 4 ofertas por feriante = 40 ofertas.
                foreach ($productos->random(4) as $producto) {
                    Oferta::create([
                        'feriante_id' => $feriante->id,
                        'producto_id' => $producto->id,
                        'punto_retiro_id' => $puntos->random()->id,
                        'precio' => fake()->randomElement([800, 1500, 2500, 3500, 5000, 7500, 12000]),
                        'cantidad_disponible' => fake()->numberBetween(0, 30),
                        'disponible_desde' => today()->subDays(fake()->numberBetween(0, 3)),
                        'vigente_hasta' => today()->addDays(fake()->numberBetween(-1, 7)),
                        'promocion' => fake()->boolean(20),
                        'entrega_domicilio' => fake()->boolean(30),
                    ]);
                }
            }
        }
    }
}
