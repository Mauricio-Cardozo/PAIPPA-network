<?php

namespace App\Enums;

/**
 * Roles de los usuarios del sistema.
 * Se guarda el valor (string) en la columna users.rol.
 */
enum Rol: string
{
    case Admin = 'admin';
    case Feriante = 'feriante';
    case Revendedor = 'revendedor';

    /** Nombre para mostrar en la interfaz. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Feriante => 'Feriante',
            self::Revendedor => 'Revendedor',
        };
    }
}
