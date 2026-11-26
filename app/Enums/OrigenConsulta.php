<?php

namespace App\Enums;

/**
 * De dónde viene una consulta por producto (HU15).
 * Se guarda el valor (string) en la columna consultas_producto.origen.
 */
enum OrigenConsulta: string
{
    case Busqueda = 'busqueda';
    case Whatsapp = 'whatsapp';

    /** Nombre para mostrar en la interfaz. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Busqueda => 'Búsqueda en el catálogo',
            self::Whatsapp => 'Contacto por WhatsApp',
        };
    }
}
