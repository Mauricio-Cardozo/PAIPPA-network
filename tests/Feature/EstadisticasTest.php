<?php

namespace Tests\Feature;

use App\Models\ClicWhatsapp;
use App\Models\ConsultaProducto;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EstadisticasTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function buscar_en_el_catalogo_registra_una_consulta_por_producto_y_por_dia(): void
    {
        $oferta = Oferta::factory()->create(['producto_id' => Producto::factory()->create(['nombre' => 'Tomate'])]);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/?q=Tomate');
        $this->actingAs($user)->get('/?q=Tomate'); // el mismo usuario, el mismo día: no cuenta dos veces

        $this->assertSame(1, ConsultaProducto::count());
        $consulta = ConsultaProducto::sole();
        $this->assertSame($oferta->producto_id, $consulta->producto_id);
        $this->assertSame(ConsultaProducto::ORIGEN_BUSQUEDA, $consulta->origen);
        $this->assertSame('user:'.$user->id, $consulta->visitante);
    }

    #[Test]
    public function un_visitante_se_guarda_con_un_hash_y_no_con_el_id_de_sesion(): void
    {
        Oferta::factory()->create(['producto_id' => Producto::factory()->create(['nombre' => 'Tomate'])]);

        $this->get('/?q=Tomate');

        $consulta = ConsultaProducto::sole();
        $this->assertNull($consulta->user_id);
        $this->assertSame(64, strlen($consulta->visitante));
        $this->assertNotSame(session()->getId(), $consulta->visitante);
    }

    #[Test]
    public function el_catalogo_sin_busqueda_no_registra_consultas(): void
    {
        Oferta::factory()->create();

        $this->get('/');

        $this->assertSame(0, ConsultaProducto::count());
    }

    #[Test]
    public function un_clic_de_whatsapp_registra_la_consulta_con_origen_whatsapp(): void
    {
        $user = User::factory()->create();
        $oferta = Oferta::factory()->create();

        $this->actingAs($user)->get(route('contacto.whatsapp', $oferta));

        $consulta = ConsultaProducto::sole();
        $this->assertSame(ConsultaProducto::ORIGEN_WHATSAPP, $consulta->origen);
        $this->assertSame('user:'.$user->id, $consulta->visitante);
    }

    #[Test]
    public function el_panel_muestra_los_indicadores_del_periodo(): void
    {
        $admin = User::factory()->admin()->create();
        $oferta = Oferta::factory()->create(['producto_id' => Producto::factory()->create(['nombre' => 'Mandioca'])]);
        $revendedor = User::factory()->create();

        ClicWhatsapp::create(['user_id' => $revendedor->id, 'feriante_id' => $oferta->feriante_id, 'oferta_id' => $oferta->id]);
        ConsultaProducto::create(['producto_id' => $oferta->producto_id, 'visitante' => 'user:1', 'origen' => 'busqueda', 'fecha' => today()]);

        $this->actingAs($admin)->get(route('admin.estadisticas'))
            ->assertOk()
            ->assertSee('Productos más consultados')
            ->assertSee('Mandioca')
            ->assertSee($oferta->feriante->nombre)
            ->assertSee('Clics en WhatsApp en el período');
    }

    #[Test]
    public function el_filtro_de_fechas_deja_afuera_lo_de_otros_dias(): void
    {
        $admin = User::factory()->admin()->create();
        $producto = Producto::factory()->create(['nombre' => 'Zapallo viejo']);
        ConsultaProducto::create(['producto_id' => $producto->id, 'visitante' => 'x', 'origen' => 'busqueda', 'fecha' => today()->subDays(60)]);

        $this->actingAs($admin)->get(route('admin.estadisticas'))->assertDontSee('Zapallo viejo');
        $this->actingAs($admin)->get(route('admin.estadisticas', ['desde' => today()->subDays(90)->toDateString(), 'hasta' => today()->toDateString()]))
            ->assertSee('Zapallo viejo');
    }

    #[Test]
    public function un_rango_invertido_da_error_y_un_revendedor_no_entra(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.estadisticas', ['desde' => '2026-11-20', 'hasta' => '2026-11-01']))
            ->assertSessionHasErrors('hasta');

        $this->actingAs(User::factory()->create())->get(route('admin.estadisticas'))->assertForbidden();
    }
}
