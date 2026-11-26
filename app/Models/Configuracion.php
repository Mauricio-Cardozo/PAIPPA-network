<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * Parámetros del sistema como clave y valor, que el PAIPPA puede cambiar sin
 * tocar el código. Ejemplo: Configuracion::valor('dias_vigencia_oferta', '7').
 */
#[Fillable(['clave', 'valor', 'descripcion'])]
class Configuracion extends Model
{
    protected $table = 'configuracion';

    /** El valor de una clave, o $porDefecto si no existe o está vacía. */
    public static function valor(string $clave, ?string $porDefecto = null): ?string
    {
        $valor = static::where('clave', $clave)->value('valor');

        return ($valor === null || $valor === '') ? $porDefecto : $valor;
    }
}
