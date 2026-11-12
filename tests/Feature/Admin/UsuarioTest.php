<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UsuarioTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function el_admin_bloquea_y_queda_registrado_quien_y_cuando(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 25)->setTime(9, 30));
        $admin = User::factory()->admin()->create(['name' => 'Admin Uno']);
        $revendedor = User::factory()->create();

        $this->actingAs($admin)->patch(route('admin.usuarios.bloquear', $revendedor))->assertSessionHas('exito');

        $revendedor->refresh();
        $this->assertTrue($revendedor->estaBloqueado());
        $this->assertSame($admin->id, $revendedor->bloqueado_por);

        $this->actingAs($admin)->get(route('admin.usuarios.index'))
            ->assertSee('Bloqueado')
            ->assertSee('25/11/2026 09:30')
            ->assertSee('por Admin Uno');
    }

    #[Test]
    public function al_desbloquear_se_limpian_los_datos(): void
    {
        $admin = User::factory()->admin()->create();
        $revendedor = User::factory()->create(['bloqueado_at' => now(), 'bloqueado_por' => $admin->id]);

        $this->actingAs($admin)->patch(route('admin.usuarios.desbloquear', $revendedor));

        $revendedor->refresh();
        $this->assertNull($revendedor->bloqueado_at);
        $this->assertNull($revendedor->bloqueado_por);
    }

    #[Test]
    public function un_usuario_bloqueado_no_puede_iniciar_sesion(): void
    {
        $user = User::factory()->create(['bloqueado_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Tu cuenta está bloqueada. Comunicate con el PAIPPA.']);

        $this->assertGuest();
    }

    #[Test]
    public function con_la_contrasena_incorrecta_no_se_revela_que_esta_bloqueado(): void
    {
        $user = User::factory()->create(['bloqueado_at' => now()]);

        $this->post('/login', ['email' => $user->email, 'password' => 'otra'])
            ->assertSessionHasErrors(['email' => __('auth.failed')]);
    }

    #[Test]
    public function si_lo_bloquean_con_la_sesion_abierta_lo_saca(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/profile')->assertOk();

        $user->forceFill(['bloqueado_at' => now()])->save();

        $this->get('/profile')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    #[Test]
    public function no_se_puede_bloquear_a_si_mismo_ni_a_otro_admin(): void
    {
        $admin = User::factory()->admin()->create();
        $otroAdmin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch(route('admin.usuarios.bloquear', $admin))->assertSessionHas('error');
        $this->actingAs($admin)->patch(route('admin.usuarios.bloquear', $otroAdmin))->assertSessionHas('error');

        $this->assertFalse($admin->fresh()->estaBloqueado());
        $this->assertFalse($otroAdmin->fresh()->estaBloqueado());
    }

    #[Test]
    public function busca_y_filtra_por_rol(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Admin Principal']);
        User::factory()->create(['name' => 'Rocío Revendedora']);
        User::factory()->feriante()->create(['name' => 'Fabián Feriante']);

        $this->actingAs($admin)->get(route('admin.usuarios.index', ['rol' => 'feriante']))
            ->assertSee('Fabián Feriante')->assertDontSee('Rocío Revendedora');

        $this->actingAs($admin)->get(route('admin.usuarios.index', ['buscar' => 'Rocío']))
            ->assertSee('Rocío Revendedora')->assertDontSee('Fabián Feriante');

        $this->actingAs($admin)->get(route('admin.usuarios.index', ['rol' => 'superadmin']))
            ->assertSessionHasErrors('rol');
    }

    #[Test]
    public function un_revendedor_no_puede_bloquear(): void
    {
        $otro = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->patch(route('admin.usuarios.bloquear', $otro))
            ->assertForbidden();
    }
}
