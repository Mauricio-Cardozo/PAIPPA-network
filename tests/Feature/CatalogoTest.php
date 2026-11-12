<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CatalogoTest extends TestCase
{
    use RefreshDatabase;

    private function oferta(string $producto, array $datos = [], ?Categoria $categoria = null): Oferta
    {
        return Oferta::factory()->create(array_merge([
            'producto_id' => Producto::factory()->create([
                'nombre' => $producto,
                'categoria_id' => $categoria?->id ?? Categoria::factory(),
            ])->id,
        ], $datos));
    }

    #[Test]
    public function muestra_solo_las_ofertas_publicadas(): void
    {
        $this->oferta('Tomate perita');
        $this->oferta('Zapallo sin stock', ['cajas_disponibles' => 0]);
        $this->oferta('Mandioca oculta', ['feriante_id' => Feriante::factory()->sinValidar()]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Tomate perita')
            ->assertDontSee('Zapallo sin stock')
            ->assertDontSee('Mandioca oculta');
    }

    #[Test]
    public function el_visitante_no_ve_el_telefono_del_feriante(): void
    {
        $oferta = $this->oferta('Banana');

        $this->get('/')
            ->assertDontSee($oferta->feriante->telefono_whatsapp)
            ->assertSee('Iniciá sesión para contactar');
    }

    #[Test]
    public function muestra_el_precio_estimado_con_formato_argentino(): void
    {
        $this->oferta('Naranja', ['precio_caja' => 12500]);

        $this->get('/')->assertSee('$ 12.500,00')->assertSee('Precio estimado');
    }

    #[Test]
    public function filtra_por_texto_categoria_feria_y_precio(): void
    {
        $frutas = Categoria::factory()->create(['nombre' => 'Frutas']);
        $naranja = $this->oferta('Naranja', ['precio_caja' => 5000], $frutas);
        $this->oferta('Mandarina', ['precio_caja' => 20000], $frutas);
        $this->oferta('Lechuga', ['precio_caja' => 3000]);

        $this->get('/?q=Naran')->assertSee('Naranja')->assertDontSee('Mandarina')->assertDontSee('Lechuga');
        $this->get('/?categoria='.$frutas->id)->assertSee('Naranja')->assertSee('Mandarina')->assertDontSee('Lechuga');
        $this->get('/?precio_max=6000')->assertSee('Naranja')->assertSee('Lechuga')->assertDontSee('Mandarina');

        // Combinados.
        $this->get('/?categoria='.$frutas->id.'&precio_max=6000')
            ->assertSee('Naranja')->assertDontSee('Mandarina')->assertDontSee('Lechuga');

        $feria = $naranja->puntoRetiro->feria_id;
        $this->get('/?feria='.$feria)->assertSee('Naranja')->assertDontSee('Lechuga');
    }

    #[Test]
    public function avisa_cuando_no_hay_resultados(): void
    {
        $this->oferta('Tomate');

        $this->get('/?q=kiwi')->assertSee('No encontramos ofertas con esos filtros');
    }

    public static function parametrosRaros(): array
    {
        return [
            'categoría con texto' => ['?categoria=abc'],
            'inyección SQL en la búsqueda' => ["?q=' OR 1=1 --"],
            'inyección SQL en la feria' => ['?feria=1%20OR%201=1'],
            'precio con texto' => ['?precio_max=hola'],
            'precio negativo' => ['?precio_max=-5'],
            'parámetro como lista' => ['?q[]=a&categoria[]=1'],
            'texto muy largo' => ['?q='.str_repeat('a', 500)],
        ];
    }

    #[Test]
    #[DataProvider('parametrosRaros')]
    public function un_parametro_raro_no_rompe_el_catalogo(string $consulta): void
    {
        $this->oferta('Tomate');

        $this->get('/'.$consulta)->assertOk();
    }

    #[Test]
    public function la_inyeccion_sql_no_devuelve_todas_las_ofertas(): void
    {
        $this->oferta('Tomate');

        $this->get("/?q=' OR 1=1 --")->assertDontSee('Tomate');
    }

    #[Test]
    public function los_datos_se_muestran_escapados(): void
    {
        $this->oferta('<script>alert(1)</script>');

        $this->get('/')
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    #[Test]
    public function no_hay_consultas_n_mas_1(): void
    {
        // Mismo número de consultas con 2 ofertas que con 12.
        $contar = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get('/')->assertOk();

            return count(DB::getQueryLog());
        };

        Oferta::factory()->count(2)->create();
        $conDos = $contar();

        Oferta::factory()->count(10)->create();
        $conDoce = $contar();

        $this->assertSame($conDos, $conDoce);
    }

    #[Test]
    public function pagina_de_a_12_y_conserva_los_filtros(): void
    {
        $punto = PuntoRetiro::factory()->create();
        $feriante = Feriante::factory()->create(['feria_id' => $punto->feria_id]);
        Oferta::factory()->count(13)->create(['feriante_id' => $feriante->id, 'punto_retiro_id' => $punto->id, 'precio_caja' => 1000]);

        $this->get('/?precio_max=2000')
            ->assertSee('13 ofertas')
            ->assertSee('precio_max=2000&amp;page=2', false);
    }
}
