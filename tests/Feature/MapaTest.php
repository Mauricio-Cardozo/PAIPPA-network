<?php

namespace Tests\Feature;

use App\Models\Oferta;
use App\Models\PuntoRetiro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MapaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function devuelve_los_puntos_en_json(): void
    {
        $oferta = Oferta::factory()->create();
        $punto = $oferta->puntoRetiro;

        $this->getJson(route('mapa.puntos'))
            ->assertOk()
            ->assertJsonStructure(['data' => [['id', 'nombre', 'direccion', 'horario', 'latitud', 'longitud', 'feria' => ['id', 'nombre'], 'ofertas_publicadas', 'url_catalogo']]])
            ->assertJsonPath('data.0.id', $punto->id)
            ->assertJsonPath('data.0.ofertas_publicadas', 1)
            ->assertJsonPath('data.0.feria.nombre', $punto->feria->nombre);
    }

    #[Test]
    public function no_incluye_puntos_dados_de_baja_ni_telefonos(): void
    {
        $oferta = Oferta::factory()->create();
        $baja = PuntoRetiro::factory()->create(['nombre' => 'Punto borrado']);
        $baja->delete();

        $response = $this->getJson(route('mapa.puntos'));

        $response->assertJsonCount(1, 'data')->assertJsonMissing(['nombre' => 'Punto borrado']);
        $this->assertStringNotContainsString($oferta->feriante->telefono_whatsapp, $response->getContent());
    }

    #[Test]
    public function no_cuenta_las_ofertas_de_un_feriante_sin_validar(): void
    {
        $oferta = Oferta::factory()->create();
        $oferta->feriante->forceFill(['validado_at' => null])->save();

        $this->getJson(route('mapa.puntos'))
            ->assertJsonPath('data.0.ofertas_publicadas', 0);
    }
}
