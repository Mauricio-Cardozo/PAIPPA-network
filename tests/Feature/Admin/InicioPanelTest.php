<?php

namespace Tests\Feature\Admin;

use App\Models\Feriante;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-140: el inicio del panel muestra los contadores y enlaza a cada ABM. */
class InicioPanelTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function muestra_los_contadores_con_los_feriantes_pendientes(): void
    {
        Feriante::factory()->count(2)->create();
        Feriante::factory()->sinValidar()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.inicio'))
            ->assertOk()
            ->assertViewHas('totalFeriantes', 3)
            ->assertViewHas('feriantesPendientes', 1)
            ->assertSee('1 pendiente de validar');
    }

    #[Test]
    public function enlaza_a_todos_los_abm_del_panel(): void
    {
        $respuesta = $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.inicio'))
            ->assertDontSee('Próximamente');

        foreach (['feriantes', 'ferias', 'puntos-retiro', 'productos', 'categorias', 'ofertas'] as $abm) {
            $respuesta->assertSee(route("admin.{$abm}.index"), false);
        }
    }

    #[Test]
    public function un_comprador_no_entra_al_panel(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.inicio'))
            ->assertForbidden();
    }
}
