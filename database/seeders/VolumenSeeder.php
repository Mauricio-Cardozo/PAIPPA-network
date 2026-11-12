<?php

namespace Database\Seeders;

use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Illuminate\Database\Seeder;

/**
 * Carga 2000 ofertas para medir el rendimiento del catálogo (PAI-65).
 * NO se ejecuta con --seed: se llama a mano, en una base de prueba:
 *   sail artisan db:seed --class=VolumenSeeder
 */
class VolumenSeeder extends Seeder
{
    public function run(): void
    {
        if (Producto::count() === 0 || Feriante::count() === 0) {
            $this->call([CategoriaSeeder::class, FeriaSeeder::class]);
        }

        $productos = Producto::pluck('id');
        $feriantes = Feriante::all(['id', 'feria_id']);
        $puntosPorFeria = PuntoRetiro::all(['id', 'feria_id'])->groupBy('feria_id');

        $filas = [];
        $ahora = now();

        for ($i = 0; $i < 2000; $i++) {
            $feriante = $feriantes->random();

            $filas[] = [
                'feriante_id' => $feriante->id,
                'producto_id' => $productos->random(),
                'punto_retiro_id' => $puntosPorFeria[$feriante->feria_id]->random()->id,
                'precio_caja' => fake()->numberBetween(2000, 40000),
                'cajas_disponibles' => fake()->numberBetween(0, 40),
                'vigente_desde' => today()->subDays(fake()->numberBetween(0, 30))->toDateString(),
                'vigente_hasta' => today()->addDays(fake()->numberBetween(-20, 10))->toDateString(),
                'created_at' => $ahora,
                'updated_at' => $ahora->copy()->subMinutes($i),
            ];
        }

        // De a 500 filas por INSERT para que sea rápido.
        foreach (array_chunk($filas, 500) as $lote) {
            Oferta::insert($lote);
        }
    }
}
