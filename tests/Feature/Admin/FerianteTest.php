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
                'procedencia' => 'Colonia Pastoril',
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
                'procedencia' => 'Colonia Pastoril',
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
        $this->assertTrue($feriante->fresh()->validadoPor->is($admin)); // queda quién lo validó

        $this->actingAs($admin)->patch(route('admin.feriantes.invalidar', $feriante));
        $this->assertFalse($feriante->fresh()->estaValidado());
        $this->assertNull($feriante->fresh()->validado_por);
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
                'procedencia' => 'Colonia Pastoril',
                'telefono_whatsapp' => '5493704123456',
                'validado_at' => now()->toDateTimeString(),
                'validado_por' => 1,
            ]);

        $feriante = Feriante::firstOrFail();
        $this->assertFalse($feriante->estaValidado());
        $this->assertNull($feriante->validado_por);
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

    #[Test]
    public function la_procedencia_es_obligatoria(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.feriantes.store'), [
                'feria_id' => Feria::factory()->create()->id,
                'nombre' => 'Rosa',
                'telefono_whatsapp' => '5493704123456',
            ])->assertSessionHasErrors('procedencia');
    }

    #[Test]
    public function la_autorizacion_de_whatsapp_se_guarda_y_sin_marcar_es_no(): void
    {
        $admin = User::factory()->admin()->create();
        $feria = Feria::factory()->create();
        $datos = ['feria_id' => $feria->id, 'procedencia' => 'Colonia Pastoril', 'telefono_whatsapp' => '5493704123456'];

        $this->actingAs($admin)->post(route('admin.feriantes.store'), $datos + ['nombre' => 'Autoriza', 'autoriza_whatsapp' => '1']);
        $this->actingAs($admin)->post(route('admin.feriantes.store'), $datos + ['nombre' => 'No autoriza']);

        $this->assertTrue(Feriante::where('nombre', 'Autoriza')->firstOrFail()->autoriza_whatsapp);
        $this->assertFalse(Feriante::where('nombre', 'No autoriza')->firstOrFail()->autoriza_whatsapp);

        $this->actingAs($admin)->get(route('admin.feriantes.index'))->assertSee('No autoriza mostrarlo');
    }
}
