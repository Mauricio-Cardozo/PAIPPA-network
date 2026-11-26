<?php

namespace Tests\Feature;

use App\Models\Configuracion;
use App\Models\Feriante;
use App\Models\HistorialOferta;
use App\Models\MedioPago;
use App\Models\Oferta;
use App\Models\PrecioReferencia;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-152: las tablas del diseño que salieron del relevamiento. */
class TablasDisenoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // "Hoy" es el viernes 20/11/2026: la semana empieza el lunes 16/11.
        $this->travelTo(now()->setDate(2026, 11, 20)->setTime(10, 0));
    }

    #[Test]
    public function un_feriante_acepta_varios_medios_de_pago(): void
    {
        $feriante = Feriante::factory()->create();
        $efectivo = MedioPago::factory()->create(['nombre' => 'Efectivo']);
        $transferencia = MedioPago::factory()->create(['nombre' => 'Transferencia']);

        $feriante->mediosPago()->attach([$efectivo->id, $transferencia->id]);

        $this->assertEqualsCanonicalizing(['Efectivo', 'Transferencia'], $feriante->mediosPago->pluck('nombre')->all());
        $this->assertTrue($efectivo->feriantes->first()->is($feriante));

        // Si se elimina el feriante, se borran sus vínculos con los medios de pago.
        $feriante->forceDelete();
        $this->assertDatabaseCount('feriante_medio_pago', 0);
        $this->assertDatabaseCount('medios_pago', 2);
    }

    #[Test]
    public function el_precio_tope_se_guarda_uno_por_producto_y_semana(): void
    {
        $this->assertSame('2026-11-16', PrecioReferencia::inicioDeSemana('2026-11-20'));

        $tope = PrecioReferencia::factory()->create();

        $this->expectException(UniqueConstraintViolationException::class);
        PrecioReferencia::factory()->create([
            'producto_id' => $tope->producto_id,
            'semana' => $tope->semana->toDateString(),
        ]);
    }

    #[Test]
    public function el_tope_vigente_es_el_de_la_semana_mas_reciente_que_ya_empezo(): void
    {
        $producto = Producto::factory()->create();
        PrecioReferencia::factory()->create(['producto_id' => $producto->id, 'semana' => '2026-11-09', 'precio_tope' => 1000]);
        PrecioReferencia::factory()->create(['producto_id' => $producto->id, 'semana' => '2026-11-16', 'precio_tope' => 1200]);
        // La de la semana que viene todavía no vale.
        PrecioReferencia::factory()->create(['producto_id' => $producto->id, 'semana' => '2026-11-23', 'precio_tope' => 1500]);

        $this->assertSame('1200.00', $producto->precioTopeVigente->precio_tope);
        $this->assertNull(Producto::factory()->create()->precioTopeVigente);
    }

    #[Test]
    public function se_advierte_si_la_oferta_supera_el_precio_tope(): void
    {
        $producto = Producto::factory()->create();
        PrecioReferencia::factory()->create(['producto_id' => $producto->id, 'precio_tope' => 1000]);

        $cara = Oferta::factory()->create(['producto_id' => $producto->id, 'precio' => 1500]);
        $justa = Oferta::factory()->create(['producto_id' => $producto->id, 'precio' => 1000]);
        $sinTope = Oferta::factory()->create(['precio' => 99999]);

        $this->assertTrue($cara->superaPrecioTope());
        $this->assertFalse($justa->superaPrecioTope());
        $this->assertFalse($sinTope->superaPrecioTope());
    }

    #[Test]
    public function el_alta_de_una_oferta_queda_en_el_historial_con_su_autor(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $oferta = Oferta::factory()->create(['precio' => 1500]);

        $registro = $oferta->historial()->sole();
        $this->assertSame('alta', $registro->accion);
        $this->assertSame('1500.00', $registro->precio);
        $this->assertTrue($registro->user->is($admin));
        $this->assertNotNull($registro->created_at);
    }

    #[Test]
    public function solo_los_cambios_de_precio_cantidad_o_vigencia_quedan_en_el_historial(): void
    {
        $oferta = Oferta::factory()->create(['precio' => 1500, 'cantidad_disponible' => 10]);

        $oferta->update(['promocion' => true]);
        $this->assertSame(1, $oferta->historial()->count());

        $oferta->update(['precio' => 1800]);
        $oferta->update(['cantidad_disponible' => 4]);

        $this->assertSame(['alta', 'cambio', 'cambio'], $oferta->historial()->orderBy('id')->pluck('accion')->all());
        $this->assertSame('4.00', $oferta->historial()->latest('id')->first()->cantidad_disponible);
        // Sin sesión (por ejemplo, un seeder) el autor queda vacío.
        $this->assertNull(HistorialOferta::first()->user_id);
    }

    #[Test]
    public function la_configuracion_devuelve_el_valor_o_el_valor_por_defecto(): void
    {
        Configuracion::create(['clave' => 'comercializacion_whatsapp', 'valor' => '5493700000000']);
        Configuracion::create(['clave' => 'vacia', 'valor' => '']);

        $this->assertSame('5493700000000', Configuracion::valor('comercializacion_whatsapp'));
        $this->assertSame('x', Configuracion::valor('vacia', 'x'));
        $this->assertNull(Configuracion::valor('no_existe'));
    }
}
