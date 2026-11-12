<?php

namespace Database\Factories;

use App\Models\Feria;
use App\Models\PuntoRetiro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PuntoRetiro>
 */
class PuntoRetiroFactory extends Factory
{
    public function definition(): array
    {
        return [
            'feria_id' => Feria::factory(),
            'nombre' => 'Punto '.fake()->unique()->word(),
            'direccion' => fake()->streetAddress(),
            'horario' => 'De 7 a 12',
            // Alrededor de Formosa Capital.
            'latitud' => fake()->randomFloat(7, -26.21, -26.15),
            'longitud' => fake()->randomFloat(7, -58.20, -58.14),
        ];
    }
}
