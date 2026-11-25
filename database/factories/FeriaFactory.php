<?php

namespace Database\Factories;

use App\Enums\TipoFeria;
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
            'tipo' => TipoFeria::Fija,
            'direccion' => 'Calle '.fake()->numberBetween(1, 50).' y '.fake()->numberBetween(1, 50),
            'barrio' => 'Barrio '.fake()->lastName(),
            'dias' => 'Sábados',
            'fecha' => null,
            'hora_inicio' => '07:00',
            'hora_fin' => '12:00',
            'descripcion' => null,
        ];
    }

    /** Feria itinerante: una fecha puntual en lugar de días fijos. */
    public function itinerante(): static
    {
        return $this->state(fn () => [
            'tipo' => TipoFeria::Itinerante,
            'dias' => null,
            'fecha' => now()->addWeek()->toDateString(),
        ]);
    }
}
