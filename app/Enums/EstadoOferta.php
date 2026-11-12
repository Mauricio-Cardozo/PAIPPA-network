<?php

namespace App\Enums;

/**
 * Estado de una oferta. Lo calcula Oferta::estado con las MISMAS reglas que
 * el scope publicadas(), para que el panel y el catálogo nunca se contradigan.
 */
enum EstadoOferta: string
{
    case Publicada = 'publicada';
    case Programada = 'programada';
    case Vencida = 'vencida';
    case SinStock = 'sin_stock';
    case FerianteSinValidar = 'feriante_sin_validar';
    case DadaDeBaja = 'dada_de_baja';

    public function etiqueta(): string
    {
        return match ($this) {
            self::Publicada => 'Publicada',
            self::Programada => 'Empieza más adelante',
            self::Vencida => 'Vencida',
            self::SinStock => 'Sin stock',
            self::FerianteSinValidar => 'Feriante sin validar',
            self::DadaDeBaja => 'Producto, punto o feriante dado de baja',
        };
    }

    /** Clases de Tailwind para la etiqueta de color. */
    public function color(): string
    {
        return match ($this) {
            self::Publicada => 'bg-verde-100 text-verde-800',
            self::Programada => 'bg-blue-100 text-blue-800',
            self::SinStock, self::FerianteSinValidar => 'bg-zapallo-100 text-zapallo-700',
            self::Vencida, self::DadaDeBaja => 'bg-gray-200 text-gray-700',
        };
    }
}
