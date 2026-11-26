<?php

namespace Database\Factories;

use App\Models\PrecioReferencia;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PrecioReferencia>
 */
class PrecioReferenciaFactory extends Factory
{
    /** Por defecto, el tope de la semana actual. */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'precio_tope' => fake()->randomElement([1000, 1500, 2500, 4000]),
            'semana' => PrecioReferencia::inicioDeSemana(today()),
        ];
    }
}
