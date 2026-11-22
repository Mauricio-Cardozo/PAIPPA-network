<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Carga los datos de ejemplo de la base.
     */
    public function run(): void
    {
        // Usuarios de prueba: solo en el entorno local, nunca en producción.
        if (app()->isLocal()) {
            User::firstOrNew(['email' => 'test@example.com'])->forceFill([
                'name' => 'Revendedor de prueba',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'rol' => Rol::Revendedor,
            ])->save();

            User::firstOrNew(['email' => 'admin@paippa.test'])->forceFill([
                'name' => 'Administrador PAIPPA',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'rol' => Rol::Admin,
            ])->save();
        }
    }
}
