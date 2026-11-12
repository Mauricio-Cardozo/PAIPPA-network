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
    public function la_pagina_del_mapa_carga(): void
    {
        $this->get(route('mapa'))->assertOk()->assertSee('id="mapa"', false);
    }

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
}
