<?php

namespace Tests\Feature;

use App\Enums\OrigenConsulta;
use App\Models\Categoria;
use App\Models\ConsultaProducto;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-89 (HU15): las búsquedas y los clics de WhatsApp cuentan como consultas por producto. */
class RegistroConsultasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-27 10:00:00');
    }

    private function ofertaDe(string $nombre, ?Categoria $categoria = null): Oferta
    {
        $producto = Producto::factory()->create(array_filter([
            'nombre' => $nombre,
            'categoria_id' => $categoria?->id,
        ]));

        return Oferta::factory()->create(['producto_id' => $producto->id]);
    }

    #[Test]
    public function buscar_por_texto_registra_los_productos_encontrados(): void
    {
        $miel = $this->ofertaDe('Miel de monte');
        $this->ofertaDe('Zapallo');

        $this->get(route('catalogo', ['q' => 'miel']))->assertOk();

        $this->assertDatabaseCount('consultas_producto', 1);
        $consulta = ConsultaProducto::firstOrFail();
        $this->assertSame($miel->producto_id, $consulta->producto_id);
        $this->assertSame(OrigenConsulta::Busqueda, $consulta->origen);
        $this->assertSame('2026-11-27', $consulta->fecha->toDateString());
        $this->assertNull($consulta->user_id);
    }

    #[Test]
    public function filtrar_por_categoria_tambien_cuenta(): void
    {
        $verduras = Categoria::factory()->create();
        $this->ofertaDe('Zapallo', $verduras);
        $this->ofertaDe('Mandioca', $verduras);
        $this->ofertaDe('Miel');

        $this->get(route('catalogo', ['categoria' => $verduras->id]))->assertOk();

        $this->assertDatabaseCount('consultas_producto', 2);
    }

    #[Test]
    public function ver_el_catalogo_sin_buscar_no_registra_nada(): void
    {
        $this->ofertaDe('Miel');

        $this->get(route('catalogo'))->assertOk();
        // Filtrar solo por precio no es buscar un producto.
        $this->get(route('catalogo', ['precio_max' => 99999]))->assertOk();

        $this->assertDatabaseCount('consultas_producto', 0);
    }

    #[Test]
    public function la_misma_busqueda_el_mismo_dia_cuenta_una_sola_vez(): void
    {
        $this->ofertaDe('Miel');
        $comprador = User::factory()->create();

        $this->actingAs($comprador)->get(route('catalogo', ['q' => 'miel']));
        $this->actingAs($comprador)->get(route('catalogo', ['q' => 'miel']));

        $this->assertDatabaseCount('consultas_producto', 1);
        $this->assertDatabaseHas('consultas_producto', ['user_id' => $comprador->id, 'visitante' => 'user:'.$comprador->id]);

        // Al otro día vuelve a contar.
        $this->travelTo('2026-11-28 10:00:00');
        $this->actingAs($comprador)->get(route('catalogo', ['q' => 'miel']));
        $this->assertDatabaseCount('consultas_producto', 2);
    }

    #[Test]
    public function solo_cuenta_la_primera_pagina(): void
    {
        foreach (range(1, 14) as $i) {
            $this->ofertaDe("Miel {$i}");
        }

        $this->get(route('catalogo', ['q' => 'miel', 'page' => 2]))->assertOk();
        $this->assertDatabaseCount('consultas_producto', 0);

        $this->get(route('catalogo', ['q' => 'miel']))->assertOk();
        $this->assertDatabaseCount('consultas_producto', 12);
    }

    #[Test]
    public function el_clic_de_whatsapp_registra_una_consulta_del_producto(): void
    {
        $oferta = $this->ofertaDe('Miel');
        $comprador = User::factory()->create();

        $this->actingAs($comprador)->get(route('contacto.whatsapp', $oferta))->assertRedirect();
        $this->actingAs($comprador)->get(route('contacto.whatsapp', $oferta))->assertRedirect();

        $this->assertDatabaseCount('consultas_producto', 1);
        $this->assertDatabaseHas('consultas_producto', [
            'producto_id' => $oferta->producto_id,
            'user_id' => $comprador->id,
            'origen' => OrigenConsulta::Whatsapp->value,
        ]);
    }

    #[Test]
    public function buscar_y_contactar_son_consultas_distintas(): void
    {
        $oferta = $this->ofertaDe('Miel');
        $comprador = User::factory()->create();

        $this->actingAs($comprador)->get(route('catalogo', ['q' => 'miel']));
        $this->actingAs($comprador)->get(route('contacto.whatsapp', $oferta));

        $this->assertDatabaseCount('consultas_producto', 2);
    }
}
