<?php

namespace Tests\Feature;

use App\Enums\Unidad;
use App\Models\Oferta;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfertaCantidadTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function la_oferta_guarda_cantidad_con_decimales_en_la_unidad_del_producto(): void
    {
        $producto = Producto::factory()->create(['unidad' => Unidad::Kilo]);
        $oferta = Oferta::factory()->create([
            'producto_id' => $producto->id,
            'cantidad_disponible' => 2.5,
            'precio' => 2500,
        ]);

        $oferta->refresh();

        $this->assertSame('2.50', $oferta->cantidad_disponible);
        $this->assertSame(Unidad::Kilo, $oferta->producto->unidad);
        $this->assertFalse($oferta->promocion);
        $this->assertNull($oferta->cerrada_at);
    }

    #[Test]
    public function una_oferta_cerrada_guarda_lo_vendido_y_la_fecha(): void
    {
        $oferta = Oferta::factory()->cerrada()->create();

        $this->assertNotNull($oferta->fresh()->cerrada_at);
        $this->assertSame('10.00', $oferta->fresh()->cantidad_vendida);
    }
}
