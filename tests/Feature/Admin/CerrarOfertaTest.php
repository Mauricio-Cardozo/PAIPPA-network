<?php

namespace Tests\Feature\Admin;

use App\Enums\EstadoOferta;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-150 (RN-17): cerrar una oferta con la cantidad vendida. */
class CerrarOfertaTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-27 10:00:00');
        $this->admin = User::factory()->admin()->create();
    }

    private function oferta(): Oferta
    {
        return Oferta::factory()->create([
            'cantidad_disponible' => 10,
            'disponible_desde' => '2026-11-25',
            'vigente_hasta' => '2026-11-30',
        ]);
    }

    #[Test]
    public function el_admin_cierra_la_oferta_con_la_cantidad_vendida(): void
    {
        $oferta = $this->oferta();

        $this->actingAs($this->admin)
            ->patch(route('admin.ofertas.cerrar', $oferta), ['cantidad_vendida' => '7,5'])
            ->assertRedirect()
            ->assertSessionHas('exito');

        $oferta->refresh();
        $this->assertSame('7.50', $oferta->cantidad_vendida);
        $this->assertSame('2026-11-27 10:00:00', $oferta->cerrada_at->format('Y-m-d H:i:s'));
        $this->assertSame(EstadoOferta::Cerrada, $oferta->estado);
    }

    #[Test]
    public function la_cantidad_vendida_es_opcional(): void
    {
        $oferta = $this->oferta();

        $this->actingAs($this->admin)
            ->patch(route('admin.ofertas.cerrar', $oferta), ['cantidad_vendida' => ''])
            ->assertSessionHasNoErrors();

        $oferta->refresh();
        $this->assertNotNull($oferta->cerrada_at);
        $this->assertNull($oferta->cantidad_vendida);
    }

    #[Test]
    public function la_oferta_cerrada_deja_de_estar_publicada(): void
    {
        $oferta = $this->oferta();
        $this->assertTrue(Oferta::publicadas()->whereKey($oferta->id)->exists());

        $this->actingAs($this->admin)->patch(route('admin.ofertas.cerrar', $oferta));

        $this->assertFalse(Oferta::publicadas()->whereKey($oferta->id)->exists());
    }

    #[Test]
    public function la_cantidad_vendida_no_puede_ser_negativa_ni_mayor_que_la_disponible(): void
    {
        $oferta = $this->oferta();

        foreach (['-1', '10,5', 'abc'] as $cantidad) {
            $this->actingAs($this->admin)
                ->patch(route('admin.ofertas.cerrar', $oferta), ['cantidad_vendida' => $cantidad])
                ->assertSessionHasErrors('cantidad_vendida');
        }

        $this->assertNull($oferta->refresh()->cerrada_at);

        // Vender todo lo disponible sí se puede.
        $this->actingAs($this->admin)
            ->patch(route('admin.ofertas.cerrar', $oferta), ['cantidad_vendida' => '10'])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function una_oferta_cerrada_no_se_vuelve_a_cerrar(): void
    {
        $oferta = $this->oferta();
        $this->actingAs($this->admin)->patch(route('admin.ofertas.cerrar', $oferta), ['cantidad_vendida' => '3']);

        $this->travelTo('2026-11-28 10:00:00');
        $this->actingAs($this->admin)
            ->patch(route('admin.ofertas.cerrar', $oferta), ['cantidad_vendida' => '5'])
            ->assertSessionHas('error');

        $oferta->refresh();
        $this->assertSame('3.00', $oferta->cantidad_vendida);
        $this->assertSame('2026-11-27', $oferta->cerrada_at->toDateString());
    }

    #[Test]
    public function el_listado_muestra_lo_vendido_de_una_oferta_cerrada(): void
    {
        $oferta = $this->oferta();
        $this->actingAs($this->admin)->patch(route('admin.ofertas.cerrar', $oferta), ['cantidad_vendida' => '4']);

        $this->actingAs($this->admin)
            ->get(route('admin.ofertas.index'))
            ->assertOk()
            ->assertSee('Cerrada el 27/11/2026')
            ->assertSee('vendido 4')
            ->assertDontSee(route('admin.ofertas.cerrar', $oferta), false);
    }

    #[Test]
    public function un_comprador_no_puede_cerrar_ofertas(): void
    {
        $oferta = $this->oferta();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.ofertas.cerrar', $oferta))
            ->assertForbidden();

        $this->assertNull($oferta->refresh()->cerrada_at);
    }
}
