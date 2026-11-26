<?php

namespace App\Support;

use App\Models\Feriante;
use App\Models\Oferta;

/**
 * Arma los enlaces wa.me para contactar a un feriante por WhatsApp con un
 * mensaje ya escrito. El mensaje no se guarda en ningún lado.
 *
 * RN-06: si el feriante no autoriza mostrar su número, el enlace va al área de
 * Comercialización del PAIPPA (número en PAIPPA_WHATSAPP_COMERCIALIZACION) y el
 * mensaje dice a qué feriante se quiere contactar.
 */
class WhatsApp
{
    /** Enlace para contactar; null si no hay a qué número escribir. */
    public static function enlace(Feriante $feriante, ?Oferta $oferta = null): ?string
    {
        $numero = self::numero($feriante);

        if ($numero === null) {
            return null;
        }

        return 'https://wa.me/'.$numero.'?text='.rawurlencode(self::mensaje($feriante, $oferta));
    }

    /** El número del feriante si lo autoriza; si no, el de Comercialización. */
    public static function numero(Feriante $feriante): ?string
    {
        if ($feriante->autoriza_whatsapp) {
            return $feriante->telefono_whatsapp;
        }

        return config('services.paippa.whatsapp_comercializacion') ?: null;
    }

    /** Si el contacto va directo al feriante (true) o a Comercialización (false). */
    public static function esDirecto(Feriante $feriante): bool
    {
        return (bool) $feriante->autoriza_whatsapp;
    }

    public static function mensaje(Feriante $feriante, ?Oferta $oferta = null): string
    {
        // A Comercialización se le dice a qué feriante se busca.
        $saludo = self::esDirecto($feriante)
            ? "Hola {$feriante->nombre}"
            : "Hola, quiero contactar a {$feriante->nombre}";

        if ($oferta === null) {
            return self::esDirecto($feriante)
                ? "{$saludo}, te encontré en PAIPPA network. ¿Qué tenés disponible esta semana?"
                : "{$saludo}, feriante de PAIPPA network. ¿Qué tiene disponible esta semana?";
        }

        $precio = number_format((float) $oferta->precio, 2, ',', '.');
        $unidad = $oferta->producto->unidad?->value;
        $detalle = "{$oferta->producto->nombre} a \${$precio} por {$unidad} (retiro en {$oferta->puntoRetiro->nombre})";

        return self::esDirecto($feriante)
            ? "{$saludo}, vi en PAIPPA network tu oferta de {$detalle}. ¿Sigue disponible?"
            : "{$saludo}, por su oferta en PAIPPA network de {$detalle}. ¿Sigue disponible?";
    }
}
