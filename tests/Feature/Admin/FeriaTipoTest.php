<?php

namespace Tests\Feature\Admin;

use App\Enums\TipoFeria;
use App\Models\Feria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-149: ferias fijas e itinerantes, según el relevamiento (RF-004). */
class FeriaTipoTest extends TestCase
{
    use RefreshDatabase;

    private function datos(array $cambios = []): array
    {
        return array_merge([
            'nombre' => 'Feria La Paz',
            'tipo' => 'fija',
            'direccion' => 'Av. Gendarmería 1200',
            'barrio' => 'La Paz',
            'dias' => 'Miércoles y sábados',
            'fecha' => '',
            'hora_inicio' => '06:00',
            'hora_fin' => '12:00',
        ], $cambios);
    }

    #[Test]
    public function el_admin_crea_una_feria_fija_con_sus_dias(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ferias.store'), $this->datos(['fecha' => '2026-12-05']))
            ->assertRedirect(route('admin.ferias.index'));

        $feria = Feria::firstOrFail();
        $this->assertSame(TipoFeria::Fija, $feria->tipo);
        $this->assertNull($feria->fecha); // la fecha no corresponde a una feria fija
        $this->assertSame('Miércoles y sábados · 06:00 a 12:00', $feria->cuando);
    }

    #[Test]
    public function el_admin_crea_una_feria_itinerante_con_su_fecha(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ferias.store'), $this->datos([
                'nombre' => 'Feria itinerante San Antonio', 'tipo' => 'itinerante',
                'barrio' => 'San Antonio', 'dias' => 'Sábados', 'fecha' => '2026-11-28',
            ]))
            ->assertRedirect(route('admin.ferias.index'));

        $feria = Feria::firstOrFail();
        $this->assertSame(TipoFeria::Itinerante, $feria->tipo);
        $this->assertSame('2026-11-28', $feria->fecha->toDateString());
        $this->assertNull($feria->dias);
    }

    #[Test]
    public function una_fija_pide_los_dias_y_una_itinerante_la_fecha(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.ferias.store'), $this->datos(['dias' => '']))
            ->assertSessionHasErrors('dias');

        $this->actingAs($admin)->post(route('admin.ferias.store'), $this->datos(['tipo' => 'itinerante']))
            ->assertSessionHasErrors('fecha');
    }

    #[Test]
    public function la_hora_de_fin_tiene_que_ser_posterior_a_la_de_inicio(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.ferias.store'), $this->datos(['hora_inicio' => '12:00', 'hora_fin' => '08:00']))
            ->assertSessionHasErrors('hora_fin');
    }

    #[Test]
    public function el_listado_muestra_el_tipo_el_barrio_y_cuando(): void
    {
        Feria::factory()->create(['nombre' => 'Feria Fija', 'barrio' => 'Centro', 'dias' => 'Sábados']);
        Feria::factory()->itinerante()->create(['nombre' => 'Feria Itinerante', 'fecha' => '2026-11-28']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.ferias.index'))
            ->assertOk()
            ->assertSee('Fija')
            ->assertSee('Centro')
            ->assertSee('Sábados · 07:00 a 12:00')
            ->assertSee('Itinerante')
            ->assertSee('28/11');
    }
}
