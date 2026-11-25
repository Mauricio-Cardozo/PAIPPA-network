<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function el_admin_crea_y_edita_un_producto(): void
    {
        $admin = User::factory()->admin()->create();
        $categoria = Categoria::factory()->create();

        $this->actingAs($admin)->post(route('admin.productos.store'), [
            'categoria_id' => $categoria->id,
            'nombre' => 'Mandioca',
            'unidad' => 'bolsa',
        ])->assertRedirect(route('admin.productos.index'));

        $producto = Producto::where('nombre', 'Mandioca')->firstOrFail();
        $this->assertTrue($producto->categoria->is($categoria));

        $this->actingAs($admin)->get(route('admin.productos.index'))->assertSee('Mandioca');
        $this->actingAs($admin)->get(route('admin.productos.edit', $producto))->assertOk();
    }

    #[Test]
    public function la_categoria_es_obligatoria_y_debe_existir(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post(route('admin.productos.store'), ['categoria_id' => 999, 'nombre' => 'X', 'unidad' => 'caja'])
            ->assertSessionHasErrors('categoria_id');
    }

    #[Test]
    public function no_se_da_de_baja_un_producto_con_ofertas(): void
    {
        $oferta = Oferta::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('admin.productos.destroy', $oferta->producto))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($oferta->producto);
    }
}
