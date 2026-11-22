<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
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
    public function no_se_puede_borrar_una_categoria_con_productos(): void
    {
        $producto = Producto::factory()->create();

        $this->expectException(QueryException::class);

        $producto->categoria->forceDelete();
    }
}
