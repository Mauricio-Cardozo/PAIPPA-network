<?php

namespace Database\Factories;

use App\Models\MedioPago;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MedioPago>
 */
class MedioPagoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => ucfirst(fake()->unique()->words(2, true)),
        ];
    }
}
