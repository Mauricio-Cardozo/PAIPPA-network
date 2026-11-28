<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Matriz de acceso: cada rol contra las rutas públicas, con sesión y de admin.
 */
class AccesoPorRolTest extends TestCase
{
    use RefreshDatabase;

    /**
     * [rol (null = visitante), ruta, resultado esperado]
     * Resultado: 200, 'login' (redirige al login) o 403.
     */
    public static function casos(): array
    {
        $rutas = [
            'catálogo' => ['/', [200, 200, 200, 200]],
            'perfil propio' => ['/profile', ['login', 200, 200, 200]],
            'panel admin' => ['/admin', ['login', 403, 403, 200]],
            'estadísticas' => ['/admin/estadisticas', ['login', 403, 403, 200]],
        ];

        $roles = [null, 'comprador', 'feriante', 'admin'];
        $casos = [];

        foreach ($rutas as $nombre => [$url, $esperados]) {
            foreach ($roles as $i => $rol) {
                $casos[($rol ?? 'visitante').' en '.$nombre] = [$rol, $url, $esperados[$i]];
            }
        }

        return $casos;
    }

    #[Test]
    #[DataProvider('casos')]
    public function cada_rol_accede_solo_a_lo_suyo(?string $rol, string $url, int|string $esperado): void
    {
        if ($rol !== null) {
            $this->actingAs(User::factory()->create(['rol' => $rol]));
        }

        $response = $this->get($url);

        if ($esperado === 'login') {
            $response->assertRedirect(route('login'));
        } else {
            $response->assertStatus($esperado);
        }
    }

    #[Test]
    public function el_admin_va_al_panel_y_el_comprador_al_catalogo_despues_del_login(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/dashboard')->assertRedirect(route('admin.inicio'));

        $comprador = User::factory()->create();
        $this->actingAs($comprador)->get('/dashboard')->assertRedirect(route('catalogo'));
    }
}
