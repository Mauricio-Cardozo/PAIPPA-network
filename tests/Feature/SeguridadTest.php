<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\PuntoRetiro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route as RutaLaravel;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * PAI-93: pruebas de seguridad (RNF-002): XSS, inyección SQL, CSRF y roles.
 * Lo que no se puede automatizar está en docs/pruebas/seguridad.md.
 */
class SeguridadTest extends TestCase
{
    use RefreshDatabase;

    private const SCRIPT = '<script>alert(1)</script>';

    #[Test]
    public function un_script_guardado_como_nombre_se_muestra_escapado(): void
    {
        $oferta = Oferta::factory()->create([
            'producto_id' => Producto::factory()->create(['nombre' => self::SCRIPT]),
            'feriante_id' => Feriante::factory()->create(['nombre' => self::SCRIPT]),
        ]);

        // Catálogo y perfil: Blade escapa con {{ }} y el script queda como texto.
        foreach ([route('catalogo'), route('feriantes.show', $oferta->feriante)] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertDontSee(self::SCRIPT, false)
                ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        }

        // Panel del admin.
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.productos.index'))
            ->assertDontSee(self::SCRIPT, false);
    }

    #[Test]
    public function la_inyeccion_sql_en_los_buscadores_no_rompe_nada(): void
    {
        Oferta::factory()->create();
        $admin = User::factory()->admin()->create();
        $ataques = ["' OR 1=1 --", "'; DROP TABLE productos; --", '1 UNION SELECT password FROM users', '%_\\'];

        foreach ($ataques as $ataque) {
            $this->get(route('catalogo', ['q' => $ataque, 'categoria' => $ataque, 'feria' => $ataque, 'precio_max' => $ataque]))
                ->assertOk();

            $this->actingAs($admin)->get(route('admin.feriantes.index', ['buscar' => $ataque]))->assertOk();
        }

        // Las tablas siguen ahí y con sus datos: Eloquent usa consultas con parámetros.
        $this->assertSame(1, Producto::count());
        $this->assertSame(1, Oferta::count());
    }

    #[Test]
    public function ninguna_ruta_del_panel_es_accesible_para_un_comprador_ni_un_visitante(): void
    {
        $parametros = $this->registrosDePrueba();
        $comprador = User::factory()->create();
        $rutas = collect(Route::getRoutes()->getRoutes())
            ->filter(fn (RutaLaravel $ruta) => str_starts_with((string) $ruta->getName(), 'admin.'));

        $this->assertGreaterThan(30, $rutas->count(), 'No se encontraron las rutas del panel.');

        foreach ($rutas as $ruta) {
            $metodo = collect($ruta->methods())->first(fn (string $m) => $m !== 'HEAD');
            $url = route($ruta->getName(), array_intersect_key($parametros, array_flip($ruta->parameterNames())));

            // Un visitante va al login.
            $this->call($metodo, $url)->assertRedirect(route('login'));

            // Un comprador recibe 403, sea para ver o para cambiar algo.
            $this->actingAs($comprador)->call($metodo, $url)->assertForbidden();
            $this->app['auth']->forgetGuards();
        }
    }

    #[Test]
    public function todos_los_formularios_que_cambian_datos_llevan_csrf(): void
    {
        $sinToken = [];

        foreach (File::allFiles(resource_path('views')) as $archivo) {
            preg_match_all('/<form\b[^>]*>.*?<\/form>/is', $archivo->getContents(), $formularios);

            foreach ($formularios[0] as $formulario) {
                $cambiaDatos = preg_match('/method\s*=\s*"post"/i', $formulario);

                if ($cambiaDatos && ! str_contains($formulario, '@csrf')) {
                    $sinToken[] = $archivo->getRelativePathname();
                }
            }
        }

        $this->assertSame([], $sinToken, 'Formularios POST sin @csrf.');
    }

    #[Test]
    public function ninguna_vista_imprime_sin_escapar(): void
    {
        $conSalidaCruda = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($archivo) => str_contains($archivo->getContents(), '{!!'))
            ->map(fn ($archivo) => $archivo->getRelativePathname())
            ->values()
            ->all();

        $this->assertSame([], $conSalidaCruda, 'Vistas con {!! !!}.');
    }

    /** Un registro de cada tipo, para completar los parámetros de las rutas. */
    private function registrosDePrueba(): array
    {
        $oferta = Oferta::factory()->create();

        return [
            'categoria' => Categoria::factory()->create()->id,
            'producto' => $oferta->producto_id,
            'feria' => Feria::factory()->create()->id,
            'puntoRetiro' => PuntoRetiro::factory()->create()->id,
            'feriante' => $oferta->feriante_id,
            'oferta' => $oferta->id,
            'usuario' => User::factory()->create()->id,
        ];
    }
}
