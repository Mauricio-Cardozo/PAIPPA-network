<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\Rol;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'rol' => Rol::class,
            'bloqueado_at' => 'datetime',
        ];
    }

    /*
     * El rol NO está en #[Fillable]: así nadie puede crearse un admin
     * mandando "rol=admin" en un formulario. Se asigna a mano en el código.
     */

    public function esAdmin(): bool
    {
        return $this->rol === Rol::Admin;
    }

    public function esRevendedor(): bool
    {
        return $this->rol === Rol::Revendedor;
    }

    public function esFeriante(): bool
    {
        return $this->rol === Rol::Feriante;
    }
}
