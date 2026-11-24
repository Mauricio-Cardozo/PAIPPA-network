<?php

namespace App\Enums;

/**
 * Unidad en la que se vende un producto. La cantidad y el precio de cada
 * oferta se expresan en la unidad de su producto (por ejemplo, 12 kilos a
 * $ 2.500 el kilo).
 */
enum Unidad: string
{
    case Kilo = 'kilo';
    case Atado = 'atado';
    case Bolsa = 'bolsa';
    case Caja = 'caja';
    case Docena = 'docena';
    case Unidad = 'unidad';
    case Maple = 'maple';
    case Frasco = 'frasco';

    /** Nombre para mostrar en las pantallas. */
    public function etiqueta(): string
    {
        return ucfirst($this->value);
    }
}
