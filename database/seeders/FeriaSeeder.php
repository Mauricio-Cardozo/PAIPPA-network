<?php

namespace Database\Seeders;

use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Illuminate\Database\Seeder;

/**
 * Ferias, puntos de retiro, feriantes y ofertas de EJEMPLO.
 * Los nombres y teléfonos son ficticios; las coordenadas están en Formosa Capital.
 */
class FeriaSeeder extends Seeder
{
    public function run(): void
    {
        $ferias = [
            [
                'nombre' => 'Feria Centro',
                'dias_horario' => 'Miércoles y sábados de 6 a 12',
                'puntos' => [
                    ['Plaza San Martín', 'Av. 25 de Mayo y Moreno', 'De 6 a 12', -26.1857, -58.1756],
                    ['Galpón Centro', 'Belgrano 450', 'De 7 a 11', -26.1834, -58.1730],
                ],
            ],
            [
                'nombre' => 'Feria Barrio San Miguel',
                'dias_horario' => 'Martes y viernes de 7 a 12',
                'puntos' => [
                    ['Playón San Miguel', 'Av. Kirchner 2800', 'De 7 a 12', -26.1950, -58.1960],
                    ['Salón vecinal San Miguel', 'Calle 10 y 23', 'De 8 a 11', -26.1985, -58.2010],
                ],
            ],
            [
                'nombre' => 'Feria Barrio Guadalupe',
                'dias_horario' => 'Jueves y domingos de 6 a 11',
                'puntos' => [
                    ['Plaza Guadalupe', 'Av. Italia 1500', 'De 6 a 11', -26.1700, -58.1880],
                    ['Polideportivo Guadalupe', 'Av. Gutnisky 3100', 'De 7 a 10', -26.1660, -58.1950],
                ],
            ],
        ];

        $productos = Producto::all();

        foreach ($ferias as $i => $datos) {
            $feria = Feria::create([
                'nombre' => $datos['nombre'],
                'dias_horario' => $datos['dias_horario'],
            ]);

            $puntos = collect($datos['puntos'])->map(fn ($p) => PuntoRetiro::create([
                'feria_id' => $feria->id,
                'nombre' => $p[0],
                'direccion' => $p[1],
                'horario' => $p[2],
                'latitud' => $p[3],
                'longitud' => $p[4],
            ]));

            // 10 feriantes en total (4 + 3 + 3); los dos últimos sin validar.
            $cantidad = $i === 0 ? 4 : 3;

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
                        'precio_caja' => fake()->randomElement([3500, 5000, 7500, 9000, 12000, 15500, 18000]),
                        'cajas_disponibles' => fake()->numberBetween(0, 30),
                        'vigente_desde' => today()->subDays(fake()->numberBetween(0, 3)),
                        'vigente_hasta' => today()->addDays(fake()->numberBetween(-1, 7)),
                    ]);
                }
            }
        }
    }
}
