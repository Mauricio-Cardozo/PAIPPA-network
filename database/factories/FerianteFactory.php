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
            'procedencia' => 'Colonia '.fake()->lastName(),
            // Número ficticio con formato válido (549 + 10 dígitos).
            'telefono_whatsapp' => '549370'.fake()->numerify('#######'),
            'autoriza_whatsapp' => true,
            'descripcion' => null,
            'validado_at' => now(),
        ];
    }

    /** Feriante que no autoriza mostrar su número (RN-06). */
    public function sinAutorizarWhatsapp(): static
    {
        return $this->state(['autoriza_whatsapp' => false]);
    }

    /** Feriante pendiente de validar. */
    public function sinValidar(): static
    {
        return $this->state(['validado_at' => null]);
    }
}
