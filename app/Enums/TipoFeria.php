<?php

namespace App\Enums;

/**
 * Tipo de feria, según el relevamiento con el PAIPPA: las fijas se hacen
 * siempre en el mismo lugar ciertos días de la semana; las itinerantes, en
 * un barrio distinto en una fecha puntual.
 */
enum TipoFeria: string
{
    case Fija = 'fija';
    case Itinerante = 'itinerante';

    /** Nombre para mostrar en las pantallas. */
    public function etiqueta(): string
    {
        return ucfirst($this->value);
    }
}
