<?php

namespace Tests\Feature\Auth;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_el_registro_crea_un_revendedor_aunque_se_mande_otro_rol(): void
    {
        $this->post('/register', [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'telefono' => '+54 9 370 400-0000',
            'rol' => 'admin',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'intruso@example.com')->firstOrFail();
        $this->assertSame(Rol::Revendedor, $user->rol);
        $this->assertSame('5493704000000', $user->telefono);
    }

    public function test_un_correo_repetido_muestra_el_error(): void
    {
        User::factory()->create(['email' => 'ana@example.com']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email' => 'El correo electrónico ya está registrado.']);
        $this->assertGuest();
    }
}
