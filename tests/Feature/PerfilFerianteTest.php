<?php

namespace Tests\Feature;

use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\PuntoRetiro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PerfilFerianteTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_feriante_validado_tiene_perfil_publico(): void
    {
        $oferta = Oferta::factory()->create();
        $vencida = Oferta::factory()->vencida()->create(['feriante_id' => $oferta->feriante_id, 'punto_retiro_id' => $oferta->punto_retiro_id]);

        $this->get(route('feriantes.show', $oferta->feriante))
            ->assertOk()
            ->assertSee($oferta->feriante->nombre)
            ->assertSee($oferta->producto->nombre)
            ->assertDontSee($vencida->producto->nombre)
            ->assertSee($oferta->puntoRetiro->direccion)
            ->assertDontSee($oferta->feriante->telefono_whatsapp);
    }

    #[Test]
    public function un_feriante_sin_validar_da_404(): void
    {
        $this->get(route('feriantes.show', Feriante::factory()->sinValidar()->create()))->assertNotFound();
        $this->get('/feriantes/9999')->assertNotFound();
    }

    #[Test]
    public function el_perfil_no_tiene_consultas_n_mas_1(): void
    {
        $punto = PuntoRetiro::factory()->create();
        $feriante = Feriante::factory()->create(['feria_id' => $punto->feria_id]);

        $contar = function () use ($feriante): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get(route('feriantes.show', $feriante))->assertOk();

            return count(DB::getQueryLog());
        };

        Oferta::factory()->count(2)->create(['feriante_id' => $feriante->id, 'punto_retiro_id' => $punto->id]);
        $conDos = $contar();
        Oferta::factory()->count(8)->create(['feriante_id' => $feriante->id, 'punto_retiro_id' => $punto->id]);

        $this->assertSame($conDos, $contar());
    }

    #[Test]
    public function la_tarjeta_del_catalogo_enlaza_al_perfil(): void
    {
        $oferta = Oferta::factory()->create();

        $this->get('/')->assertSee(route('feriantes.show', $oferta->feriante), false);
    }
}
