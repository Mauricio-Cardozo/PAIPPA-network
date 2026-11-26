<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feria;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** PAI-64: filtros del catálogo con parámetros y paginación. */
class CatalogoFiltrosTest extends TestCase
{
    use RefreshDatabase;

    private Oferta $tomate;

    private Oferta $lechuga;

    protected function setUp(): void
    {
        parent::setUp();

        $hortalizas = Categoria::factory()->create(['nombre' => 'Hortalizas']);
        $hoja = Categoria::factory()->create(['nombre' => 'Verduras de hoja']);
        $feriaCentro = Feria::factory()->create(['nombre' => 'Feria Centro']);
        $feriaNorte = Feria::factory()->create(['nombre' => 'Feria Norte']);

        $this->tomate = Oferta::factory()->create([
            'producto_id' => Producto::factory()->create(['nombre' => 'Tomate perita', 'categoria_id' => $hortalizas->id])->id,
            'punto_retiro_id' => PuntoRetiro::factory()->create(['feria_id' => $feriaCentro->id])->id,
            'precio' => 2500,
        ]);
        $this->lechuga = Oferta::factory()->create([
            'producto_id' => Producto::factory()->create(['nombre' => 'Lechuga criolla', 'categoria_id' => $hoja->id])->id,
            'punto_retiro_id' => PuntoRetiro::factory()->create(['feria_id' => $feriaNorte->id])->id,
            'precio' => 900,
        ]);
    }

    private function catalogo(array $parametros)
    {
        return $this->get(route('catalogo', $parametros))->assertOk();
    }

    #[Test]
    public function sin_filtros_muestra_todas_las_ofertas_publicadas(): void
    {
        $this->catalogo([])->assertSee('Tomate perita')->assertSee('Lechuga criolla');
    }

    #[Test]
    public function filtra_por_texto_del_producto(): void
    {
        $this->catalogo(['q' => 'toma'])->assertSee('Tomate perita')->assertDontSee('Lechuga criolla');
    }

    #[Test]
    public function filtra_por_categoria(): void
    {
        $this->catalogo(['categoria' => $this->lechuga->producto->categoria_id])
            ->assertSee('Lechuga criolla')->assertDontSee('Tomate perita');
    }

    #[Test]
    public function filtra_por_feria_del_punto_de_retiro(): void
    {
        $this->catalogo(['feria' => $this->tomate->puntoRetiro->feria_id])
            ->assertSee('Tomate perita')->assertDontSee('Lechuga criolla');
    }

    #[Test]
    public function filtra_por_precio_maximo_y_acepta_coma(): void
    {
        $this->catalogo(['precio_max' => '1000,50'])->assertSee('Lechuga criolla')->assertDontSee('Tomate perita');
    }

    #[Test]
    public function los_filtros_se_combinan(): void
    {
        $this->catalogo(['q' => 'tomate', 'feria' => $this->lechuga->puntoRetiro->feria_id])
            ->assertDontSee('Tomate perita')->assertDontSee('Lechuga criolla')
            ->assertSee('No encontramos ofertas');
    }

    public static function parametrosRaros(): array
    {
        return [
            'categoría con texto' => [['categoria' => 'abc']],
            'feria negativa' => [['feria' => '-1']],
            'precio con texto' => [['precio_max' => 'mucho']],
            'inyección SQL' => [['q' => "' OR 1=1 --"]],
            'arreglo en lugar de texto' => [['categoria' => ['1', '2']]],
            'texto muy largo' => [['q' => str_repeat('a', 500)]],
        ];
    }

    #[Test]
    #[DataProvider('parametrosRaros')]
    public function un_parametro_raro_no_rompe_el_catalogo(array $parametros): void
    {
        // Responde 200 (nunca 500) y no muestra ofertas de más por una inyección.
        $respuesta = $this->catalogo($parametros);

        if (isset($parametros['q'])) {
            $respuesta->assertDontSee('Tomate perita')->assertDontSee('Lechuga criolla');
        }
    }

    #[Test]
    public function la_paginacion_conserva_los_filtros(): void
    {
        Oferta::factory()->count(13)->create([
            'producto_id' => $this->tomate->producto_id,
            'punto_retiro_id' => $this->tomate->punto_retiro_id,
        ]);

        $this->catalogo(['q' => 'tomate'])->assertSee('q=tomate&amp;page=2', false);
    }

    #[Test]
    public function el_formulario_lista_las_categorias_y_ferias(): void
    {
        $this->catalogo([])->assertSee('Verduras de hoja')->assertSee('Feria Norte');
    }
}
