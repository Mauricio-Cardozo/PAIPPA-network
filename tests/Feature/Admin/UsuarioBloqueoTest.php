<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/** HU13: gestión y bloqueo de usuarios (PAI-83 y PAI-84). */
class UsuarioBloqueoTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function el_admin_bloquea_y_queda_registrado_quien_y_cuando(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Uno']);
        $comprador = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.usuarios.bloquear', $comprador));

        $comprador->refresh();
        $this->assertTrue($comprador->estaBloqueado());
        $this->assertTrue($comprador->bloqueadoPor->is($admin));

        $this->actingAs($admin)->get(route('admin.usuarios.index'))
            ->assertSee('Bloqueado')
            ->assertSee('por Admin Uno');
    }

    #[Test]
    public function al_desbloquear_se_limpia_el_registro_del_bloqueo(): void
    {
        $admin = User::factory()->admin()->create();
        $comprador = User::factory()->create();
        $comprador->forceFill(['bloqueado_at' => now(), 'bloqueado_por' => $admin->id])->save();

        $this->actingAs($admin)->patch(route('admin.usuarios.desbloquear', $comprador));

        $comprador->refresh();
        $this->assertFalse($comprador->estaBloqueado());
        $this->assertNull($comprador->bloqueado_por);
    }

    #[Test]
    public function no_se_puede_bloquear_a_uno_mismo_ni_a_otro_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $otroAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch(route('admin.usuarios.bloquear', $admin))
            ->assertSessionHas('error');
        $this->actingAs($admin)->patch(route('admin.usuarios.bloquear', $otroAdmin))
            ->assertSessionHas('error');

        $this->assertFalse($admin->fresh()->estaBloqueado());
        $this->assertFalse($otroAdmin->fresh()->estaBloqueado());
    }

    #[Test]
    public function un_usuario_bloqueado_no_puede_iniciar_sesion(): void
    {
        $bloqueado = User::factory()->create(['bloqueado_at' => now()]);

        $this->post('/login', ['email' => $bloqueado->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => __('auth.blocked')]);

        $this->assertGuest();
    }

    #[Test]
    public function con_una_contrasena_incorrecta_no_se_revela_el_bloqueo(): void
    {
        $bloqueado = User::factory()->create(['bloqueado_at' => now()]);

        $this->post('/login', ['email' => $bloqueado->email, 'password' => 'incorrecta'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
    }

    #[Test]
    public function si_lo_bloquean_con_la_sesion_abierta_lo_saca_del_sistema(): void
    {
        $comprador = User::factory()->create();
        $this->actingAs($comprador)->get(route('profile.edit'))->assertOk();

        $comprador->forceFill(['bloqueado_at' => now()])->save();

        $this->actingAs($comprador)->get(route('profile.edit'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => __('auth.blocked')]);
        $this->assertGuest();
    }

    #[Test]
    public function el_listado_filtra_por_rol_y_busca_por_correo(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'Carla Compradora', 'email' => 'carla@ejemplo.test']);
        User::factory()->feriante()->create(['name' => 'Fede Feriante']);

        $this->actingAs($admin)->get(route('admin.usuarios.index', ['rol' => 'feriante']))
            ->assertSee('Fede Feriante')
            ->assertDontSee('Carla Compradora');

        $this->actingAs($admin)->get(route('admin.usuarios.index', ['buscar' => 'carla@']))
            ->assertSee('Carla Compradora')
            ->assertDontSee('Fede Feriante');

        // Un rol inventado no rompe la página: se ignora el filtro.
        $this->actingAs($admin)->get(route('admin.usuarios.index', ['rol' => 'xyz']))->assertOk();
    }

    #[Test]
    public function un_comprador_no_entra_a_la_gestion_de_usuarios(): void
    {
        $comprador = User::factory()->create();
        $otro = User::factory()->create();

        $this->actingAs($comprador)->get(route('admin.usuarios.index'))->assertForbidden();
        $this->actingAs($comprador)->patch(route('admin.usuarios.bloquear', $otro))->assertForbidden();
        $this->assertFalse($otro->fresh()->estaBloqueado());
    }
}
