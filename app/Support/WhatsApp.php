<?php

namespace App\Support;

use App\Models\Feriante;
use App\Models\Oferta;

/**
 * Arma los enlaces wa.me para contactar a un feriante por WhatsApp
 * con un mensaje ya escrito. No se guarda el mensaje en ningún lado.
 */
class WhatsApp
{
    public static function enlace(Feriante $feriante, ?Oferta $oferta = null): string
    {
        return 'https://wa.me/'.$feriante->telefono_whatsapp.'?text='.rawurlencode(self::mensaje($feriante, $oferta));
    }

    public static function mensaje(Feriante $feriante, ?Oferta $oferta = null): string
    {
        if ($oferta === null) {
            return "Hola {$feriante->nombre}, te encontré en PAIPPA network. ¿Qué tenés disponible esta semana?";
        }

        $precio = number_format((float) $oferta->precio_caja, 2, ',', '.');
        $unidad = $oferta->producto->unidad;

        return "Hola {$feriante->nombre}, vi en PAIPPA network tu oferta de {$oferta->producto->nombre} "
            ."a \${$precio} por {$unidad} (retiro en {$oferta->puntoRetiro->nombre}). ¿Sigue disponible?";
    }
}
