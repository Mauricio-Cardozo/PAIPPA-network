<?php

namespace Tests\Feature;

use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-61 y PAI-51: el catálogo público muestra solo las ofertas publicadas. */
class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 11, 20)->setTime(10, 0));
    }

    /** Oferta de un producto con nombre conocido, para buscarlo en la página. */
    private function ofertaDe(string $producto, ?Feriante $feriante = null, array $datos = []): Oferta
    {
        return Oferta::factory()->create([
            'producto_id' => Producto::factory()->create(['nombre' => $producto]),
            ...($feriante ? ['feriante_id' => $feriante->id] : []),
            ...$datos,
        ]);
    }

    #[Test]
    public function un_visitante_ve_las_ofertas_publicadas(): void
    {
        $oferta = $this->ofertaDe('Mandioca');

        $this->get(route('catalogo'))
            ->assertOk()
            ->assertViewIs('catalogo.index')
            ->assertSee('Mandioca')
            ->assertSee($oferta->feriante->nombre)
            ->assertSee('Iniciá sesión para contactar');
    }

    #[Test]
    public function no_muestra_las_ofertas_vencidas_sin_stock_ni_cerradas(): void
    {
        $this->ofertaDe('Zapallo vencido', datos: ['disponible_desde' => '2026-11-10', 'vigente_hasta' => '2026-11-19']);
        $this->ofertaDe('Tomate sin stock', datos: ['cantidad_disponible' => 0]);
        $this->ofertaDe('Lechuga cerrada', datos: ['cerrada_at' => now()]);
        $this->ofertaDe('Banana publicada');

        $this->get(route('catalogo'))
            ->assertSee('Banana publicada')
            ->assertDontSee('Zapallo vencido')
            ->assertDontSee('Tomate sin stock')
            ->assertDontSee('Lechuga cerrada');
    }

    #[Test]
    public function las_ofertas_de_un_feriante_sin_validar_no_aparecen_en_el_catalogo(): void
    {
        $this->ofertaDe('Huevos de campo', Feriante::factory()->sinValidar()->create());

        $this->get(route('catalogo'))
            ->assertOk()
            ->assertDontSee('Huevos de campo')
            ->assertSee('No encontramos ofertas');
    }

    #[Test]
    public function pagina_de_a_12_ofertas(): void
    {
        Oferta::factory()->count(13)->create();

        $this->get(route('catalogo'))
            ->assertViewHas('ofertas', fn ($ofertas) => $ofertas->count() === 12 && $ofertas->total() === 13);
    }

    #[Test]
    public function la_cantidad_de_consultas_no_crece_con_las_ofertas(): void
    {
        Oferta::factory()->create();
        $conUna = $this->contarConsultas();

        Oferta::factory()->count(5)->create();
        $conSeis = $this->contarConsultas();

        // Con eager loading son las mismas consultas para 1 o 6 ofertas (sin N+1).
        $this->assertSame($conUna, $conSeis);
    }

    private function contarConsultas(): int
    {
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get(route('catalogo'))->assertOk();
        DB::disableQueryLog();

        return count(DB::getQueryLog());
    }
}
