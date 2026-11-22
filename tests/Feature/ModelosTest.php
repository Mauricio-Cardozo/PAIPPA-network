<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use Database\Seeders\CategoriaSeeder;
use Database\Seeders\FeriaSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelosTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function una_categoria_tiene_productos(): void
    {
        $categoria = Categoria::factory()->create();
        Producto::factory()->count(2)->create(['categoria_id' => $categoria->id]);

        $this->assertCount(2, $categoria->productos);
    }

    #[Test]
    public function las_relaciones_de_feria_feriante_y_oferta_funcionan(): void
    {
        $oferta = Oferta::factory()->create();
        $feriante = $oferta->feriante;
        $feria = $feriante->feria;

        $this->assertTrue($feriante->ofertas->contains($oferta));
        $this->assertTrue($oferta->puntoRetiro->feria->is($feria));
        $this->assertCount(1, $feria->puntosRetiro);
        $this->assertTrue($feria->feriantes->contains($feriante));
    }

    #[Test]
    public function no_se_puede_borrar_una_feria_con_puntos_de_retiro(): void
    {
        $punto = PuntoRetiro::factory()->create();

        $this->expectException(QueryException::class);

        // forceDelete es un DELETE real: la clave foránea (restrict) lo frena.
        $punto->feria->forceDelete();
    }

    #[Test]
    public function no_se_puede_borrar_una_categoria_con_productos(): void
    {
        $producto = Producto::factory()->create();

        $this->expectException(QueryException::class);

        $producto->categoria->forceDelete();
    }

    #[Test]
    public function el_seeder_carga_los_datos_de_ejemplo(): void
    {
        $this->seed(CategoriaSeeder::class);
        $this->seed(FeriaSeeder::class);

        $this->assertSame(3, Feria::count());
        $this->assertSame(6, PuntoRetiro::count());
        $this->assertSame(10, Feriante::count());
        $this->assertSame(8, Feriante::validados()->count());
        $this->assertSame(40, Oferta::count());
    }
}
