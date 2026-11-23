<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeguridadSesionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function las_paginas_privadas_no_se_guardan_en_cache(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/profile');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    #[Test]
    public function el_logout_vuelve_al_catalogo_y_cierra_la_sesion(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
        $this->get('/profile')->assertRedirect(route('login'));
    }

    #[Test]
    public function todas_las_respuestas_tienen_las_cabeceras_de_seguridad(): void
    {
        $this->get('/')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
    }

    #[Test]
    public function al_sexto_intento_fallido_se_bloquea_el_acceso(): void
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta']);
        }

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'incorrecta']);

        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString('Demasiados intentos', session('errors')->first('email'));
        $this->assertGuest();
    }
}
