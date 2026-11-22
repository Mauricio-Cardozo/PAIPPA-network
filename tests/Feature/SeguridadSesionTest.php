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
}
