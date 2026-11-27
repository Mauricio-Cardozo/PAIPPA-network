<?php

namespace Database\Factories;

use App\Enums\OrigenConsulta;
use App\Models\ConsultaProducto;
use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConsultaProducto>
 */
class ConsultaProductoFactory extends Factory
{
    /** Por defecto, la búsqueda de un invitado hoy. */
    public function definition(): array
    {
        return [
            'producto_id' => Producto::factory(),
            'user_id' => null,
            'visitante' => hash('sha256', fake()->uuid()),
            'origen' => OrigenConsulta::Busqueda,
            'fecha' => today()->toDateString(),
        ];
    }

    /** Consulta que viene de un clic en WhatsApp. */
    public function whatsapp(): static
    {
        return $this->state(['origen' => OrigenConsulta::Whatsapp]);
    }
}
