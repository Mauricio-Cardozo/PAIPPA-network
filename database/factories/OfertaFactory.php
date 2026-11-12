<?php

namespace Database\Factories;

use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Oferta>
 */
class OfertaFactory extends Factory
{
    /**
     * Por defecto la oferta está vigente (desde ayer hasta dentro de 6 días)
     * y el punto de retiro es de la misma feria que el feriante.
     */
    public function definition(): array
    {
        return [
            'feriante_id' => Feriante::factory(),
            'producto_id' => Producto::factory(),
            'punto_retiro_id' => fn (array $atributos) => PuntoRetiro::factory()->create([
                'feria_id' => Feriante::find($atributos['feriante_id'])->feria_id,
            ])->id,
            'precio_caja' => fake()->randomFloat(2, 3000, 40000),
            'cajas_disponibles' => fake()->numberBetween(1, 50),
            'vigente_desde' => today()->subDay(),
            'vigente_hasta' => today()->addDays(6),
        ];
    }

    public function sinStock(): static
    {
        return $this->state(['cajas_disponibles' => 0]);
    }

    public function vencida(): static
    {
        return $this->state([
            'vigente_desde' => today()->subDays(10),
            'vigente_hasta' => today()->subDay(),
        ]);
    }
}
