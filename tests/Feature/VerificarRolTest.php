<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Prueba el middleware 'rol' con una ruta que existe solo durante la prueba.
 */
class VerificarRolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'rol:admin,feriante'])
            ->get('/prueba-rol', fn () => 'adentro');
    }

    #[Test]
    public function deja_pasar_a_los_roles_indicados(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get('/prueba-rol')->assertOk()->assertSee('adentro');
        $this->actingAs(User::factory()->feriante()->create())->get('/prueba-rol')->assertOk();
    }

    #[Test]
    public function corta_a_los_demas_roles_con_la_pagina_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/prueba-rol')
            ->assertForbidden()
            ->assertSee('No tenés permiso para entrar acá');
    }

    #[Test]
    public function sin_sesion_manda_al_login(): void
    {
        $this->get('/prueba-rol')->assertRedirect(route('login'));
    }
}
