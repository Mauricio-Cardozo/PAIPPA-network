<?php

namespace Tests\Feature;

use App\Enums\EstadoOferta;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Reglas de negocio de las ofertas: cuándo están vigentes, cuándo se
 * publican en el catálogo y qué estado muestran en el panel.
 */
class OfertaScopesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // "Hoy" es el viernes 20/11/2026 a las 10 de la mañana.
        $this->travelTo(now()->setDate(2026, 11, 20)->setTime(10, 0));
    }

    /** IDs de las ofertas publicadas, para comparar fácil. */
    private function publicadas(): array
    {
        return Oferta::publicadas()->pluck('id')->all();
    }

    /** El estado tiene que decir lo mismo que el scope publicadas(). */
    private function assertEstadoCoincide(Oferta $oferta): void
    {
        $oferta = Oferta::withTrashed()->find($oferta->id);
        $estaPublicada = in_array($oferta->id, $this->publicadas(), true);

        $this->assertSame($estaPublicada, $oferta->estado === EstadoOferta::Publicada);
    }

    #[Test]
    public function una_oferta_vigente_con_stock_y_feriante_validado_se_publica(): void
    {
        $oferta = Oferta::factory()->create();

        $this->assertSame([$oferta->id], $this->publicadas());
        $this->assertSame(EstadoOferta::Publicada, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_sin_stock_no_se_publica(): void
    {
        $oferta = Oferta::factory()->sinStock()->create();

        $this->assertSame([], $this->publicadas());
        $this->assertSame(EstadoOferta::SinStock, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_con_menos_de_una_unidad_disponible_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['cantidad_disponible' => 0.5]);

        $this->assertSame([$oferta->id], $this->publicadas());
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_cerrada_no_se_publica(): void
    {
        $oferta = Oferta::factory()->cerrada()->create();

        $this->assertSame([], $this->publicadas());
        $this->assertSame(EstadoOferta::Cerrada, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_vence_hoy_todavia_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['vigente_hasta' => '2026-11-20']);

        $this->assertSame([$oferta->id], $this->publicadas());
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_vencio_ayer_no_se_publica(): void
    {
        $oferta = Oferta::factory()->create([
            'disponible_desde' => '2026-11-10',
            'vigente_hasta' => '2026-11-19',
        ]);

        $this->assertSame([], $this->publicadas());
        $this->assertSame(EstadoOferta::Vencida, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_empieza_hoy_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['disponible_desde' => '2026-11-20']);

        $this->assertSame([$oferta->id], $this->publicadas());
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function una_oferta_que_empieza_manana_no_se_publica(): void
    {
        $oferta = Oferta::factory()->create(['disponible_desde' => '2026-11-21']);

        $this->assertSame([], $this->publicadas());
        $this->assertSame(EstadoOferta::Programada, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function la_oferta_de_un_feriante_sin_validar_es_vigente_pero_no_se_publica(): void
    {
        $oferta = Oferta::factory()->create([
            'feriante_id' => Feriante::factory()->sinValidar(),
        ]);

        $this->assertSame(1, Oferta::vigentes()->count());
        $this->assertSame([], $this->publicadas());
        $this->assertSame(EstadoOferta::FerianteSinValidar, $oferta->fresh()->estado);
        $this->assertEstadoCoincide($oferta);
    }

    #[Test]
    public function si_se_da_de_baja_el_producto_el_punto_o_el_feriante_la_oferta_no_se_publica(): void
    {
        $sinProducto = Oferta::factory()->create();
        $sinPunto = Oferta::factory()->create();
        $sinFeriante = Oferta::factory()->create();
        $dadaDeBaja = Oferta::factory()->create();

        $sinProducto->producto->delete();
        $sinPunto->puntoRetiro->delete();
        $sinFeriante->feriante->delete();
        $dadaDeBaja->delete();

        $this->assertSame([], $this->publicadas());

        foreach ([$sinProducto, $sinPunto, $sinFeriante, $dadaDeBaja] as $oferta) {
            $this->assertSame(EstadoOferta::DadaDeBaja, Oferta::withTrashed()->find($oferta->id)->estado);
            $this->assertEstadoCoincide($oferta);
        }
    }

    #[Test]
    public function el_panel_cuenta_las_ofertas_vigentes_con_la_misma_regla_que_el_scope(): void
    {
        Oferta::factory()->count(2)->create();
        Oferta::factory()->sinStock()->create();
        Oferta::factory()->cerrada()->create();
        Oferta::factory()->create(['disponible_desde' => '2026-11-21']);

        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.inicio'))
            ->assertOk()
            ->assertViewHas('totalOfertasVigentes', 2);
    }
}
