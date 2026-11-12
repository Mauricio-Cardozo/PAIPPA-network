<?php

namespace Database\Factories;

use App\Models\Feria;
use App\Models\Feriante;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feriante>
 */
class FerianteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => null,
            'feria_id' => Feria::factory(),
            'nombre' => fake()->name(),
            // Número ficticio con formato válido (549 + 10 dígitos).
            'telefono_whatsapp' => '549370'.fake()->numerify('#######'),
            'descripcion' => null,
            'validado_at' => now(),
        ];
    }

    /** Feriante pendiente de validar. */
    public function sinValidar(): static
    {
        return $this->state(['validado_at' => null]);
    }
}
