<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) {
            $response = $this->get('/reset-password/'.$notification->token);

            $response->assertStatus(200);

            return true;
        });
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use ($user) {
            $response = $this->post('/reset-password', [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login'));

            return true;
        });
    }

    public function test_el_correo_de_recuperacion_esta_en_espanol_y_vence_en_60_minutos(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $correo = $notification->toMail($user);

            $this->assertSame('Restablecé tu contraseña', $correo->subject);
            $this->assertSame('Restablecer contraseña', $correo->actionText);
            $this->assertContains('Este enlace vence en 60 minutos.', array_map('strval', array_merge($correo->introLines, $correo->outroLines)));

            return true;
        });
    }

    public function test_no_revela_si_el_correo_esta_registrado(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $existente = $this->post('/forgot-password', ['email' => $user->email]);
        $inexistente = $this->post('/forgot-password', ['email' => 'nadie@example.com']);

        $existente->assertSessionHas('status');
        $inexistente->assertSessionHas('status', session('status'));
        $inexistente->assertSessionHasNoErrors();
    }
}
