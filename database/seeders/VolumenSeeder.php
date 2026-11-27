<?php

namespace Database\Seeders;

use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Illuminate\Database\Seeder;

/**
 * PAI-65: datos de volumen para medir el catálogo (RNF-007: menos de 3 s).
 * NO se llama desde DatabaseSeeder. Se corre a mano, después del seed normal:
 *
 *   sail artisan db:seed --class=VolumenSeeder
 *
 * Agrega 50 feriantes y 2000 ofertas, con fechas y cantidades variadas para
 * que haya ofertas publicadas, vencidas, futuras y sin stock, como en la realidad.
 */
class VolumenSeeder extends Seeder
{
    public const OFERTAS = 2000;

    public function run(): void
    {
        $ferias = Feria::all();
        $productos = Producto::pluck('id');
        $puntos = PuntoRetiro::pluck('id');

        $feriantes = Feriante::factory()
            ->count(50)
            ->sequence(fn ($s) => ['feria_id' => $ferias[$s->index % $ferias->count()]->id])
            ->create()
            ->pluck('id');

        // De a 500 filas con insert(): mucho más rápido que 2000 create().
        foreach (array_chunk(range(1, self::OFERTAS), 500) as $bloque) {
            $filas = array_map(fn () => [
                'feriante_id' => $feriantes->random(),
                'producto_id' => $productos->random(),
                'punto_retiro_id' => $puntos->random(),
                'precio' => fake()->numberBetween(500, 15000),
                'cantidad_disponible' => fake()->numberBetween(0, 50),
                'disponible_desde' => today()->subDays(fake()->numberBetween(-5, 20))->toDateString(),
                'vigente_hasta' => today()->addDays(fake()->numberBetween(-15, 10))->toDateString(),
                'promocion' => fake()->boolean(15),
                'entrega_domicilio' => fake()->boolean(25),
                'created_at' => now(),
                'updated_at' => now()->subMinutes(fake()->numberBetween(0, 20000)),
            ], $bloque);

            Oferta::insert($filas);
        }
    }
}
