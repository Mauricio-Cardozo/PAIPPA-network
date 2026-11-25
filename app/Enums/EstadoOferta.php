<?php

namespace App\Enums;

/**
 * Estado de una oferta. Lo calcula Oferta::estado con las MISMAS reglas que
 * los scopes vigentes() y publicadas(), para que el panel y el catálogo
 * nunca se contradigan.
 */
enum EstadoOferta: string
{
    case Publicada = 'publicada';
    case Programada = 'programada';
    case Vencida = 'vencida';
    case SinStock = 'sin_stock';
    case Cerrada = 'cerrada';
    case FerianteSinValidar = 'feriante_sin_validar';
    case DadaDeBaja = 'dada_de_baja';

    /** Nombre para mostrar en las pantallas. */
    public function etiqueta(): string
    {
        return match ($this) {
            self::Publicada => 'Publicada',
            self::Programada => 'Empieza más adelante',
            self::Vencida => 'Vencida',
            self::SinStock => 'Sin stock',
            self::Cerrada => 'Cerrada',
            self::FerianteSinValidar => 'Feriante sin validar',
            self::DadaDeBaja => 'Producto, punto o feriante dado de baja',
        };
    }
}
