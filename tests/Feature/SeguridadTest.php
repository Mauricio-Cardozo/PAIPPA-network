<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feria;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as Rutas;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAI-93: pruebas automáticas de seguridad.
 * El CSRF no se puede probar acá (Laravel no lo verifica en las pruebas):
 * se revisa a mano con la lista de docs/pruebas/seguridad.md.
 */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    /** Todas las rutas GET del panel, con un registro de ejemplo para los parámetros. */
    private function rutasDelPanel(): array
    {
        $oferta = Oferta::factory()->create();
        $parametros = [
            'categoria' => $oferta->producto->categoria_id,
            'producto' => $oferta->producto_id,
            'feria' => $oferta->feriante->feria_id,
            'puntoRetiro' => $oferta->punto_retiro_id,
            'feriante' => $oferta->feriante_id,
            'oferta' => $oferta->id,
        ];

        return collect(Rutas::getRoutes()->getRoutes())
            ->filter(fn (Route $ruta) => str_starts_with($ruta->uri(), 'admin') && in_array('GET', $ruta->methods()))
            ->map(fn (Route $ruta) => route($ruta->getName(), array_intersect_key($parametros, array_flip($ruta->parameterNames()))))
            ->values()
            ->all();
    }

    #[Test]
    public function ninguna_ruta_del_panel_es_accesible_sin_ser_admin(): void
    {
        $rutas = $this->rutasDelPanel();
        $this->assertGreaterThan(15, count($rutas));

        $revendedor = User::factory()->create();
        $feriante = User::factory()->feriante()->create();
        $admin = User::factory()->admin()->create();

        foreach ($rutas as $url) {
            $this->get($url)->assertRedirect(route('login'));
            $this->actingAs($revendedor)->get($url)->assertForbidden();
            $this->actingAs($feriante)->get($url)->assertForbidden();
            $this->actingAs($admin)->get($url)->assertOk();
            auth()->logout();
        }
    }

    #[Test]
    public function ninguna_accion_del_panel_se_puede_hacer_sin_ser_admin(): void
    {
        $oferta = Oferta::factory()->create();
        $revendedor = User::factory()->create();

        $acciones = [
            ['post', route('admin.categorias.store'), ['nombre' => 'X']],
            ['delete', route('admin.ofertas.destroy', $oferta), []],
            ['patch', route('admin.ofertas.stock', $oferta), ['cajas_disponibles' => 0, 'vigente_hasta' => today()->toDateString()]],
            ['patch', route('admin.feriantes.invalidar', $oferta->feriante), []],
            ['patch', route('admin.usuarios.bloquear', User::factory()->create()), []],
        ];

        foreach ($acciones as [$metodo, $url, $datos]) {
            $this->actingAs($revendedor)->{$metodo}($url, $datos)->assertForbidden();
        }

        $this->assertNotSoftDeleted($oferta);
        $this->assertTrue($oferta->feriante->fresh()->estaValidado());
    }

    #[Test]
    public function un_script_guardado_se_muestra_escapado_en_el_catalogo_el_perfil_y_el_panel(): void
    {
        $xss = '<script>alert(1)</script>';
        $oferta = Oferta::factory()->create([
            'producto_id' => Producto::factory()->create([
                'nombre' => $xss,
                'categoria_id' => Categoria::factory()->create(['nombre' => $xss]),
            ]),
        ]);
        $oferta->feriante->update(['nombre' => $xss, 'descripcion' => $xss]);
        Feria::find($oferta->feriante->feria_id)->update(['nombre' => $xss]);

        $paginas = [
            '/',
            route('feriantes.show', $oferta->feriante),
        ];

        foreach ($paginas as $url) {
            $this->get($url)->assertOk()->assertDontSee($xss, false);
        }

        $admin = User::factory()->admin()->create();
        foreach (['admin.ofertas.index', 'admin.productos.index', 'admin.categorias.index', 'admin.feriantes.index', 'admin.ferias.index'] as $ruta) {
            $this->actingAs($admin)->get(route($ruta))->assertOk()->assertDontSee($xss, false);
        }

        // En el JSON del mapa el texto viaja como dato; el popup lo pone con textContent.
        $this->getJson(route('mapa.puntos'))->assertOk();
    }

    #[Test]
    public function el_registro_tiene_limite_de_intentos(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->post('/register', ['name' => 'X', 'email' => "x$i@example.com", 'password' => 'password', 'password_confirmation' => 'password']);
            auth()->logout();
        }

        $this->post('/register', ['name' => 'X', 'email' => 'x7@example.com', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertStatus(429);
    }

    #[Test]
    public function no_hay_vistas_con_salida_sin_escapar(): void
    {
        $archivos = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views')));

        foreach ($archivos as $archivo) {
            if ($archivo->isFile()) {
                $this->assertStringNotContainsString('{!!', file_get_contents($archivo->getPathname()), $archivo->getPathname().' usa {!! !!}');
            }
        }
    }

    #[Test]
    public function el_javascript_no_usa_innerhtml(): void
    {
        foreach (glob(resource_path('js/*.js')) as $archivo) {
            $this->assertStringNotContainsString('innerHTML', file_get_contents($archivo), basename($archivo).' usa innerHTML');
        }
    }
}
