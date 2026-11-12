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

class OfertaTest extends TestCase
{
    use RefreshDatabase;

    private function datosValidos(): array
    {
        $feriante = Feriante::factory()->create();

        return [
            'feriante_id' => $feriante->id,
            'producto_id' => Producto::factory()->create()->id,
            'punto_retiro_id' => PuntoRetiro::factory()->create(['feria_id' => $feriante->feria_id])->id,
            'precio_caja' => '12500.50',
            'cajas_disponibles' => 10,
            'vigente_desde' => today()->toDateString(),
            'vigente_hasta' => today()->addDays(6)->toDateString(),
        ];
    }

    #[Test]
    public function el_admin_crea_una_oferta_valida(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.ofertas.create'))->assertOk();
        $this->actingAs($admin)->post(route('admin.ofertas.store'), $this->datosValidos())
            ->assertRedirect(route('admin.ofertas.index'))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Oferta::publicadas()->count());
        $this->actingAs($admin)->get(route('admin.ofertas.index'))->assertSee('Publicada');
    }

    #[Test]
    public function rechaza_precio_cero_cajas_negativas_y_fechas_al_reves(): void
    {
        $datos = array_merge($this->datosValidos(), [
            'precio_caja' => 0,
            'cajas_disponibles' => -1,
            'vigente_desde' => today()->toDateString(),
            'vigente_hasta' => today()->subDay()->toDateString(),
        ]);

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ofertas.store'), $datos)
            ->assertSessionHasErrors(['precio_caja', 'cajas_disponibles', 'vigente_hasta']);
    }

    #[Test]
    public function no_se_carga_una_oferta_de_un_feriante_sin_validar(): void
    {
        $datos = $this->datosValidos();
        Feriante::find($datos['feriante_id'])->forceFill(['validado_at' => null])->save();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ofertas.store'), $datos)
            ->assertSessionHasErrors('feriante_id');
    }

    #[Test]
    public function el_punto_de_retiro_debe_ser_de_la_feria_del_feriante(): void
    {
        $datos = $this->datosValidos();
        $datos['punto_retiro_id'] = PuntoRetiro::factory()->create()->id; // de otra feria

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ofertas.store'), $datos)
            ->assertSessionHasErrors('punto_retiro_id');
    }

    #[Test]
    public function la_accion_rapida_actualiza_cajas_y_vigencia(): void
    {
        $oferta = Oferta::factory()->create(['cajas_disponibles' => 3]);
        $hasta = today()->addDays(10)->toDateString();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.ofertas.stock', $oferta), ['cajas_disponibles' => 0, 'vigente_hasta' => $hasta])
            ->assertSessionHasNoErrors();

        $oferta->refresh();
        $this->assertSame(0, $oferta->cajas_disponibles);
        $this->assertSame($hasta, $oferta->vigente_hasta->toDateString());
    }

    #[Test]
    public function la_accion_rapida_valida_los_datos(): void
    {
        $oferta = Oferta::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->patch(route('admin.ofertas.stock', $oferta), [
                'cajas_disponibles' => -5,
                'vigente_hasta' => $oferta->vigente_desde->copy()->subDay()->toDateString(),
            ])
            ->assertSessionHasErrors(['cajas_disponibles', 'vigente_hasta']);
    }
}
