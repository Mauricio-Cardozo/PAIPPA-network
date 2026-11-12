<?php

namespace Tests\Feature\Admin;

use App\Models\Feria;
use App\Models\Feriante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FerianteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_feriante_nuevo_queda_sin_validar_y_el_telefono_se_normaliza(): void
    {
        $feria = Feria::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.feriantes.store'), [
                'feria_id' => $feria->id,
                'nombre' => 'Rosa Benítez',
                'telefono_whatsapp' => '+54 9 370 412-3456',
            ])->assertRedirect(route('admin.feriantes.index'));

        $feriante = Feriante::firstOrFail();
        $this->assertSame('5493704123456', $feriante->telefono_whatsapp);
        $this->assertFalse($feriante->estaValidado());
    }

    #[Test]
    public function un_telefono_sin_el_549_no_se_acepta(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.feriantes.store'), [
                'feria_id' => Feria::factory()->create()->id,
                'nombre' => 'Rosa',
                'telefono_whatsapp' => '0370 154123456',
            ])->assertSessionHasErrors('telefono_whatsapp');
    }

    #[Test]
    public function el_admin_valida_e_invalida(): void
    {
        $admin = User::factory()->admin()->create();
        $feriante = Feriante::factory()->sinValidar()->create();

        $this->actingAs($admin)->patch(route('admin.feriantes.validar', $feriante));
        $this->assertTrue($feriante->fresh()->estaValidado());

        $this->actingAs($admin)->patch(route('admin.feriantes.invalidar', $feriante));
        $this->assertFalse($feriante->fresh()->estaValidado());
    }

    #[Test]
    public function solo_el_admin_valida(): void
    {
        $feriante = Feriante::factory()->sinValidar()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.feriantes.validar', $feriante))
            ->assertForbidden();

        $this->assertFalse($feriante->fresh()->estaValidado());
    }

    #[Test]
    public function validado_at_no_se_puede_mandar_en_el_formulario(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.feriantes.store'), [
                'feria_id' => Feria::factory()->create()->id,
                'nombre' => 'Rosa',
                'telefono_whatsapp' => '5493704123456',
                'validado_at' => now()->toDateTimeString(),
            ]);

        $this->assertFalse(Feriante::firstOrFail()->estaValidado());
    }

    #[Test]
    public function el_filtro_muestra_solo_los_pendientes(): void
    {
        Feriante::factory()->create(['nombre' => 'Validado Uno']);
        Feriante::factory()->sinValidar()->create(['nombre' => 'Pendiente Dos']);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.feriantes.index', ['estado' => 'pendientes']))
            ->assertSee('Pendiente Dos')
            ->assertDontSee('Validado Uno');
    }
}
