<?php

namespace Tests\Feature\Admin;

use App\Models\Feria;
use App\Models\PuntoRetiro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeriaYPuntoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function el_admin_crea_una_feria_y_un_punto_de_retiro(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.ferias.store'), [
            'nombre' => 'Feria Centro', 'tipo' => 'fija', 'direccion' => 'Av. 25 de Mayo 100',
            'barrio' => 'Centro', 'dias' => 'Sábados', 'hora_inicio' => '07:00', 'hora_fin' => '12:00',
        ])->assertRedirect(route('admin.ferias.index'));

        $feria = Feria::firstOrFail();

        $this->actingAs($admin)->post(route('admin.puntos-retiro.store'), [
            'feria_id' => $feria->id,
            'nombre' => 'Plaza',
            'direccion' => 'Av. 25 de Mayo 100',
            'horario' => 'De 7 a 12',
            'latitud' => '-26,1857',   // con coma decimal, como lo copia mucha gente
            'longitud' => '-58.1756',
        ])->assertRedirect(route('admin.puntos-retiro.index'));

        $punto = PuntoRetiro::firstOrFail();
        $this->assertEquals(-26.1857, (float) $punto->latitud);

        $this->actingAs($admin)->get(route('admin.ferias.index'))->assertSee('Feria Centro');
        $this->actingAs($admin)->get(route('admin.ferias.edit', $feria))->assertOk();
        $this->actingAs($admin)->get(route('admin.puntos-retiro.index'))->assertSee('Plaza');
        $this->actingAs($admin)->get(route('admin.puntos-retiro.edit', $punto))->assertOk();
    }

    public static function coordenadasInvalidas(): array
    {
        return [
            'latitud mayor a 90' => ['95', '-58.17', 'latitud'],
            'latitud menor a -90' => ['-91', '-58.17', 'latitud'],
            'longitud mayor a 180' => ['-26.18', '181', 'longitud'],
            'longitud con texto' => ['-26.18', 'abc', 'longitud'],
        ];
    }

    #[Test]
    #[DataProvider('coordenadasInvalidas')]
    public function no_se_aceptan_coordenadas_invalidas(string $lat, string $lng, string $campo): void
    {
        $feria = Feria::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.puntos-retiro.store'), [
                'feria_id' => $feria->id, 'nombre' => 'X', 'direccion' => 'Y', 'horario' => 'Z',
                'latitud' => $lat, 'longitud' => $lng,
            ])->assertSessionHasErrors($campo);
    }

    #[Test]
    public function no_se_da_de_baja_una_feria_con_puntos_de_retiro(): void
    {
        $punto = PuntoRetiro::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.ferias.destroy', $punto->feria))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($punto->feria);
    }

    #[Test]
    public function se_da_de_baja_una_feria_vacia(): void
    {
        $feria = Feria::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.ferias.destroy', $feria));

        $this->assertSoftDeleted($feria);
    }

    #[Test]
    public function un_comprador_no_entra_al_abm_de_ferias(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.ferias.index'))
            ->assertForbidden();
    }
}
