<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IdiomaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function los_errores_de_validacion_estan_en_espanol(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Ana',
            'email' => 'no-es-un-correo',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'El campo correo electrónico debe ser un correo electrónico válido.',
        ]);
    }

    #[Test]
    public function las_fechas_relativas_estan_en_espanol_y_en_hora_argentina(): void
    {
        $this->assertSame('America/Argentina/Cordoba', config('app.timezone'));
        $this->assertStringContainsString('hace', now()->subHours(2)->diffForHumans());
    }
}
