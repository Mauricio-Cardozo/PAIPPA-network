<?php

namespace Database\Factories;

use App\Models\Feria;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feria>
 */
class FeriaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Feria '.fake()->unique()->lastName(),
            'descripcion' => null,
            'dias_horario' => 'Sábados de 7 a 12',
        ];
    }
}
