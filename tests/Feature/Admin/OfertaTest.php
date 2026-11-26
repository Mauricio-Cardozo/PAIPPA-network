<?php

namespace Tests\Feature\Admin;

use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-57 y PAI-58: carga de ofertas y actualización de cantidad y vigencia. */
class OfertaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-25 10:00:00');
    }

    /** Datos válidos: feriante validado y punto de retiro de su misma feria. */
    private function datos(array $cambios = []): array
    {
        $feriante = Feriante::factory()->create();
        $punto = PuntoRetiro::factory()->create(['feria_id' => $feriante->feria_id]);

        return array_merge([
            'feriante_id' => $feriante->id,
            'producto_id' => Producto::factory()->create()->id,
            'punto_retiro_id' => $punto->id,
            'precio' => '2500,50',
            'cantidad_disponible' => '12,5',
            'disponible_desde' => '2026-11-25',
            'vigente_hasta' => '2026-12-01',
            'promocion' => '1',
        ], $cambios);
    }

    #[Test]
    public function el_admin_carga_una_oferta_con_cantidad_decimal_y_promocion(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ofertas.store'), $this->datos())
            ->assertRedirect(route('admin.ofertas.index'));

        $oferta = Oferta::firstOrFail();
        $this->assertSame('2500.50', $oferta->precio);
        $this->assertSame('12.50', $oferta->cantidad_disponible);
        $this->assertTrue($oferta->promocion);
        $this->assertFalse($oferta->entrega_domicilio); // casilla sin marcar
    }

    #[Test]
    public function no_se_cargan_ofertas_de_un_feriante_sin_validar(): void
    {
        $datos = $this->datos();
        Feriante::find($datos['feriante_id'])->forceFill(['validado_at' => null])->save();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ofertas.store'), $datos)
            ->assertSessionHasErrors('feriante_id');
    }

    #[Test]
    public function el_punto_de_retiro_tiene_que_ser_de_la_feria_del_feriante(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ofertas.store'), $this->datos(['punto_retiro_id' => PuntoRetiro::factory()->create()->id]))
            ->assertSessionHasErrors('punto_retiro_id');
    }

    #[Test]
    public function la_oferta_vale_como_maximo_siete_dias(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ofertas.store'), $this->datos(['vigente_hasta' => '2026-12-02']))
            ->assertSessionHasErrors('vigente_hasta');
    }

    #[Test]
    public function la_actualizacion_rapida_cambia_la_cantidad_y_la_vigencia(): void
    {
        $oferta = Oferta::factory()->create(['disponible_desde' => '2026-11-20', 'vigente_hasta' => '2026-11-26']);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.ofertas.stock', $oferta), ['cantidad_disponible' => '3', 'vigente_hasta' => '2026-12-01'])
            ->assertSessionHas('exito');

        $oferta->refresh();
        $this->assertSame('3.00', $oferta->cantidad_disponible);
        $this->assertSame('2026-12-01', $oferta->vigente_hasta->toDateString());
    }

    #[Test]
    public function la_actualizacion_rapida_no_deja_mas_de_siete_dias_desde_hoy(): void
    {
        $oferta = Oferta::factory()->create(['disponible_desde' => '2026-11-20', 'vigente_hasta' => '2026-11-26']);

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.ofertas.stock', $oferta), ['cantidad_disponible' => '3', 'vigente_hasta' => '2026-12-05'])
            ->assertSessionHasErrors('vigente_hasta');
    }

    #[Test]
    public function el_listado_muestra_el_estado_y_la_baja_es_logica(): void
    {
        $admin = User::factory()->admin()->create();
        $oferta = Oferta::factory()->create(['disponible_desde' => '2026-11-24', 'vigente_hasta' => '2026-11-28']);

        $this->actingAs($admin)->get(route('admin.ofertas.index'))
            ->assertOk()
            ->assertSee($oferta->producto->nombre)
            ->assertSee('Publicada');

        $this->actingAs($admin)->get(route('admin.ofertas.edit', $oferta))->assertOk();
        $this->actingAs($admin)->get(route('admin.ofertas.create'))->assertOk()->assertSee('Nueva oferta');

        $this->actingAs($admin)->delete(route('admin.ofertas.destroy', $oferta));
        $this->assertSoftDeleted($oferta);
    }

    #[Test]
    public function un_comprador_no_entra_a_las_ofertas_del_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.ofertas.index'))
            ->assertForbidden();
    }
}
