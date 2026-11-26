<?php

namespace Tests\Feature;

use App\Models\Feriante;
use App\Models\Oferta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-68: perfil público del feriante. */
class PerfilFerianteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_feriante_validado_muestra_su_perfil_y_sus_ofertas_publicadas(): void
    {
        $oferta = Oferta::factory()->create();
        $vencida = Oferta::factory()->create([
            'feriante_id' => $oferta->feriante_id,
            'disponible_desde' => now()->subDays(20)->toDateString(),
            'vigente_hasta' => now()->subDays(10)->toDateString(),
        ]);

        $this->get(route('feriantes.show', $oferta->feriante))
            ->assertOk()
            ->assertSee($oferta->feriante->nombre)
            ->assertSee($oferta->producto->nombre)
            ->assertDontSee($vencida->producto->nombre);
    }

    #[Test]
    public function un_feriante_sin_validar_da_404(): void
    {
        $feriante = Feriante::factory()->create(['validado_at' => null]);

        $this->get(route('feriantes.show', $feriante))->assertNotFound();
    }

    #[Test]
    public function un_feriante_dado_de_baja_da_404(): void
    {
        $feriante = Feriante::factory()->create();
        $feriante->delete();

        $this->get(route('feriantes.show', $feriante->id))->assertNotFound();
    }

    #[Test]
    public function la_cantidad_de_consultas_no_crece_con_las_ofertas(): void
    {
        $feriante = Feriante::factory()->create();
        Oferta::factory()->create(['feriante_id' => $feriante->id]);

        DB::enableQueryLog();
        $this->get(route('feriantes.show', $feriante))->assertOk();
        $conUna = count(DB::getQueryLog());

        Oferta::factory()->count(5)->create(['feriante_id' => $feriante->id]);

        DB::flushQueryLog();
        $this->get(route('feriantes.show', $feriante))->assertOk();
        $conSeis = count(DB::getQueryLog());

        $this->assertSame($conUna, $conSeis); // sin N+1
    }

    #[Test]
    public function la_tarjeta_del_catalogo_enlaza_al_perfil_del_feriante(): void
    {
        $oferta = Oferta::factory()->create();

        $this->get(route('catalogo'))
            ->assertOk()
            ->assertSee(route('feriantes.show', $oferta->feriante), false);
    }
}
