<?php

namespace Tests\Feature\Admin;

use App\Enums\Rol;
use App\Enums\Unidad;
use App\Models\ClicWhatsapp;
use App\Models\ConsultaProducto;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-90 (HU15): panel de indicadores con filtro de fechas. */
class EstadisticasTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-27 10:00:00');
        $this->admin = User::factory()->create(['rol' => Rol::Admin]);
    }

    private function clic(Feriante $feriante, string $cuando): void
    {
        $this->travelTo($cuando);
        ClicWhatsapp::create(['user_id' => $this->admin->id, 'feriante_id' => $feriante->id]);
        $this->travelTo('2026-11-27 10:00:00');
    }

    #[Test]
    public function el_comprador_no_entra(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.estadisticas'))
            ->assertForbidden();
    }

    #[Test]
    public function por_defecto_muestra_los_ultimos_30_dias(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.estadisticas'))
            ->assertOk()
            ->assertSee('Del 29/10/2026 al 27/11/2026')
            ->assertSee('No hay consultas en este período.');
    }

    #[Test]
    public function ordena_los_productos_por_cantidad_de_consultas(): void
    {
        $miel = Producto::factory()->create(['nombre' => 'Miel de monte']);
        $zapallo = Producto::factory()->create(['nombre' => 'Zapallo anco']);
        ConsultaProducto::factory()->count(3)->create(['producto_id' => $miel->id]);
        ConsultaProducto::factory()->whatsapp()->create(['producto_id' => $zapallo->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.estadisticas'))
            ->assertOk()
            ->assertSeeInOrder(['Miel de monte', '3', 'Zapallo anco', '1']);
    }

    #[Test]
    public function el_rango_de_fechas_filtra_las_consultas(): void
    {
        $viejo = Producto::factory()->create(['nombre' => 'Producto de octubre']);
        $nuevo = Producto::factory()->create(['nombre' => 'Producto de noviembre']);
        ConsultaProducto::factory()->create(['producto_id' => $viejo->id, 'fecha' => '2026-10-10']);
        // El último día del rango entra completo.
        ConsultaProducto::factory()->create(['producto_id' => $nuevo->id, 'fecha' => '2026-11-20']);

        $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['desde' => '2026-11-01', 'hasta' => '2026-11-20']))
            ->assertOk()
            ->assertSee('Producto de noviembre')
            ->assertDontSee('Producto de octubre');
    }

    #[Test]
    public function cuenta_los_clics_de_whatsapp_por_feriante(): void
    {
        $ana = Feriante::factory()->create(['nombre' => 'Ana Feriante']);
        $beto = Feriante::factory()->create(['nombre' => 'Beto Feriante']);
        $this->clic($ana, '2026-11-20 08:00:00');
        $this->clic($ana, '2026-11-20 23:59:00');
        $this->clic($beto, '2026-11-21 12:00:00');
        // Fuera del rango.
        $this->clic($beto, '2026-11-21 00:00:00');
        $this->clic($beto, '2026-11-19 23:59:00');

        $respuesta = $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['desde' => '2026-11-20', 'hasta' => '2026-11-20']))
            ->assertOk();

        $respuesta->assertSee('Ana Feriante')->assertDontSee('Beto Feriante');
        $this->assertSame(2, $respuesta->viewData('totalClics'));
    }

    #[Test]
    public function cuenta_las_ofertas_del_periodo_y_los_compradores_registrados(): void
    {
        Oferta::factory()->create(['disponible_desde' => '2026-11-18', 'vigente_hasta' => '2026-11-22']);
        Oferta::factory()->create(['disponible_desde' => '2026-11-01', 'vigente_hasta' => '2026-11-05']);

        $respuesta = $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['desde' => '2026-11-20', 'hasta' => '2026-11-27']))
            ->assertOk();

        $this->assertSame(1, $respuesta->viewData('totalOfertas'));
        // El admin no cuenta: solo los compradores registrados en el período.
        $this->assertSame(User::where('rol', Rol::Comprador)->count(), $respuesta->viewData('compradoresNuevos'));
    }

    #[Test]
    public function no_cuenta_ofertas_que_no_estuvieron_publicadas_en_el_periodo(): void
    {
        $periodo = ['desde' => '2026-11-20', 'hasta' => '2026-11-27'];
        $vigencia = ['disponible_desde' => '2026-11-18', 'vigente_hasta' => '2026-11-22'];

        // Feriante sin validar (RN-01): nunca se publicó.
        Oferta::factory()->create([...$vigencia, 'feriante_id' => Feriante::factory()->sinValidar()->create()->id]);
        // Cerrada antes de que empiece el período.
        Oferta::factory()->create([...$vigencia, 'cerrada_at' => '2026-11-19 18:00:00']);
        // Dada de baja.
        Oferta::factory()->create($vigencia)->delete();
        // Cerrada durante el período: estuvo publicada unos días, cuenta.
        Oferta::factory()->create([...$vigencia, 'cerrada_at' => '2026-11-21 12:00:00']);

        $this->assertSame(1, $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', $periodo))
            ->viewData('totalOfertas'));
    }

    #[Test]
    public function con_solo_hasta_muestra_los_30_dias_anteriores(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['hasta' => '2026-09-01']))
            ->assertOk()
            ->assertSee('Del 03/08/2026 al 01/09/2026');
    }

    #[Test]
    public function un_desde_futuro_sin_hasta_muestra_el_error(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['desde' => '2026-12-15']))
            ->assertRedirect(route('admin.estadisticas'))
            ->assertSessionHasErrors('hasta');
    }

    #[Test]
    public function muestra_lo_ofrecido_lo_vendido_y_el_monto_estimado(): void
    {
        $periodo = ['desde' => '2026-11-20', 'hasta' => '2026-11-27'];
        $vigencia = ['disponible_desde' => '2026-11-25', 'vigente_hasta' => '2026-11-30'];
        $miel = Producto::factory()->create(['nombre' => 'Miel de monte', 'unidad' => Unidad::Frasco]);
        $zapallo = Producto::factory()->create(['nombre' => 'Zapallo anco', 'unidad' => Unidad::Kilo]);

        // Se ofrecieron 10 frascos a $1.000, se agotó (stock en 0) y se cerró vendiendo los 10.
        $agotada = Oferta::factory()->create([...$vigencia, 'producto_id' => $miel->id, 'precio' => 1000, 'cantidad_disponible' => 10]);
        $agotada->update(['cantidad_disponible' => 0]);
        $agotada->forceFill(['cantidad_vendida' => 10, 'cerrada_at' => now()])->save();
        // Otra de miel a $1.200, todavía abierta: suma a lo ofrecido, no a lo vendido.
        Oferta::factory()->create([...$vigencia, 'producto_id' => $miel->id, 'precio' => 1200, 'cantidad_disponible' => 5]);
        // Zapallo sin venta informada (RN-17: solo se muestra lo ofrecido).
        Oferta::factory()->create([...$vigencia, 'producto_id' => $zapallo->id, 'precio' => 800, 'cantidad_disponible' => 20]);

        $respuesta = $this->actingAs($this->admin)->get(route('admin.estadisticas', $periodo))->assertOk();

        $filas = $respuesta->viewData('ventasPorProducto');
        $this->assertSame(['producto' => 'Miel de monte', 'unidad' => 'Frasco', 'ofrecido' => 15.0, 'vendido' => 10.0, 'monto' => 10000.0, 'precio_promedio' => 1100.0], $filas[0]);
        $this->assertSame(['producto' => 'Zapallo anco', 'unidad' => 'Kilo', 'ofrecido' => 20.0, 'vendido' => null, 'monto' => null, 'precio_promedio' => 800.0], $filas[1]);
        $this->assertSame(10000.0, $respuesta->viewData('montoTotal'));

        $respuesta->assertSee('$ 10.000,00')
            ->assertSee('Frasco: ofrecido 15,00 · vendido 10,00')
            ->assertSee('Kilo: ofrecido 20,00 · vendido —');
    }

    #[Test]
    public function cuenta_los_feriantes_activos(): void
    {
        Feriante::factory()->count(2)->create(['validado_at' => '2026-11-01 10:00:00']);
        Feriante::factory()->sinValidar()->create();
        Feriante::factory()->create(['validado_at' => '2026-11-01 10:00:00'])->delete();
        // Validado después del período.
        Feriante::factory()->create(['validado_at' => '2026-11-26 10:00:00']);

        $this->assertSame(2, $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['desde' => '2026-11-01', 'hasta' => '2026-11-25']))
            ->viewData('feriantesActivos'));
    }

    #[Test]
    public function un_rango_invalido_muestra_el_error(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['desde' => '2026-11-20', 'hasta' => '2026-11-01']))
            ->assertRedirect(route('admin.estadisticas'))
            ->assertSessionHasErrors('hasta');

        $this->actingAs($this->admin)
            ->get(route('admin.estadisticas', ['desde' => 'abc']))
            ->assertSessionHasErrors('desde');
    }
}
