<?php

namespace Tests\Feature;

use App\Enums\EstadoOferta;
use App\Models\Feriante;
use App\Models\Oferta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OfertaScopesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Se congela «hoy» para que los bordes de fecha sean exactos.
        $this->travelTo(now()->setDate(2026, 11, 20)->setTime(10, 0));
    }

    private function ids(): array
    {
        return Oferta::publicadas()->pluck('id')->all();
    }

    private function assertEstadoCoincide(Oferta $oferta): void
    {
        $oferta = Oferta::withTrashed()->with(['feriante', 'producto', 'puntoRetiro'])->find($oferta->id);
        $publicada = in_array($oferta->id, $this->ids(), true);

        $this->assertSame($publicada, $oferta->estado === EstadoOferta::Publicada, 'El estado no coincide con publicadas()');
    }

    #[Test]
    public function una_oferta_vigente_con_stock_y_feriante_validado_se_publica(): void
    {
        $oferta = Oferta::factory()->create();

        $this->assertContains($oferta->id, $this->ids());
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_sin_stock_no_se_publica(): void
    {
        $oferta = Oferta::factory()->sinStock()->create();

        $this->assertNotContains($oferta->id, $this->ids());
        $this->assertSame(EstadoOferta::SinStock, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_vence_hoy_todavia_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['vigente_hasta' => '2026-11-20']);

        $this->assertContains($oferta->id, $this->ids());
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_vencio_ayer_no_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['vigente_desde' => '2026-11-10', 'vigente_hasta' => '2026-11-19']);

        $this->assertNotContains($oferta->id, $this->ids());
        $this->assertSame(EstadoOferta::Vencida, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_empieza_manana_no_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['vigente_desde' => '2026-11-21', 'vigente_hasta' => '2026-11-28']);

        $this->assertNotContains($oferta->id, $this->ids());
        $this->assertSame(EstadoOferta::Programada, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_empieza_hoy_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['vigente_desde' => '2026-11-20', 'vigente_hasta' => '2026-11-28']);

        $this->assertContains($oferta->id, $this->ids());
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function la_oferta_de_un_feriante_sin_validar_no_se_publica(): void
    {
        // PAI-51. @todo PAI-61: extender esta prueba a la vista del catálogo.
        $oferta = Oferta::factory()->create([
            'feriante_id' => Feriante::factory()->sinValidar(),
        ]);

        $this->assertNotContains($oferta->id, $this->ids());
        $this->assertSame(EstadoOferta::FerianteSinValidar, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function si_se_da_de_baja_el_producto_el_punto_o_el_feriante_la_oferta_no_se_publica(): void
    {
        $a = Oferta::factory()->create();
        $b = Oferta::factory()->create();
        $c = Oferta::factory()->create();
        $d = Oferta::factory()->create();

        $a->producto->delete();
        $b->puntoRetiro->delete();
        $c->feriante->delete();
        $d->delete();

        foreach ([$a, $b, $c, $d] as $oferta) {
            $this->assertNotContains($oferta->id, $this->ids());
            $this->assertEstadoCoincide($oferta);
        }
    }
}
