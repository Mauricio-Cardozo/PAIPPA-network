<?php

namespace Tests\Feature\Admin;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoriaTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->admin()->create();
    }

    #[Test]
    public function el_admin_lista_crea_edita_y_da_de_baja(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.categorias.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.categorias.create'))->assertOk();

        $this->actingAs($admin)->post(route('admin.categorias.store'), ['nombre' => 'Frutas'])
            ->assertRedirect(route('admin.categorias.index'));
        $categoria = Categoria::where('nombre', 'Frutas')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.categorias.edit', $categoria))->assertOk()->assertSee('Frutas');
        $this->actingAs($admin)->put(route('admin.categorias.update', $categoria), ['nombre' => 'Frutas de estación']);
        $this->assertSame('Frutas de estación', $categoria->fresh()->nombre);

        $this->actingAs($admin)->delete(route('admin.categorias.destroy', $categoria));
        $this->assertSoftDeleted($categoria);
    }

    #[Test]
    public function un_revendedor_recibe_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.categorias.index'))
            ->assertForbidden();
    }

    #[Test]
    public function el_nombre_es_unico_entre_las_activas(): void
    {
        $admin = $this->admin();
        Categoria::factory()->create(['nombre' => 'Verduras']);

        $this->actingAs($admin)->post(route('admin.categorias.store'), ['nombre' => 'Verduras'])
            ->assertSessionHasErrors('nombre');

        // Una dada de baja libera el nombre.
        $baja = Categoria::factory()->create(['nombre' => 'Miel']);
        $baja->delete();

        $this->actingAs($admin)->post(route('admin.categorias.store'), ['nombre' => 'Miel'])
            ->assertSessionHasNoErrors();
    }

    #[Test]
    public function no_se_puede_dar_de_baja_una_categoria_con_productos(): void
    {
        $producto = Producto::factory()->create();

        $this->actingAs($this->admin())
            ->delete(route('admin.categorias.destroy', $producto->categoria))
            ->assertSessionHas('error');

        $this->assertNotSoftDeleted($producto->categoria);
    }

    #[Test]
    public function la_busqueda_filtra_por_nombre(): void
    {
        Categoria::factory()->create(['nombre' => 'Lácteos']);
        Categoria::factory()->create(['nombre' => 'Huevos']);

        $this->actingAs($this->admin())
            ->get(route('admin.categorias.index', ['buscar' => 'Láct']))
            ->assertSee('Lácteos')
            ->assertDontSee('Huevos');
    }
}
