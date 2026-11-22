<?php

namespace Tests\Feature;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RolUsuarioTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function un_usuario_nuevo_es_revendedor_por_defecto(): void
    {
        $user = User::create([
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password',
        ]);

        $this->assertSame(Rol::Revendedor, $user->fresh()->rol);
        $this->assertTrue($user->fresh()->esRevendedor());
    }

    #[Test]
    public function la_factory_crea_un_admin(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->esAdmin());
        $this->assertFalse($admin->esRevendedor());
    }

    #[Test]
    public function el_rol_no_se_puede_asignar_en_masa(): void
    {
        $user = User::create([
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'password',
            'rol' => 'admin',
        ]);

        $this->assertSame(Rol::Revendedor, $user->fresh()->rol);
    }

    #[Test]
    public function el_rol_tiene_una_etiqueta_en_espanol(): void
    {
        $this->assertSame('Administrador', Rol::Admin->etiqueta());
    }
}
