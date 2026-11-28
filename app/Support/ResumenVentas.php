<?php

namespace App\Support;

use App\Models\Oferta;
use Illuminate\Support\Collection;

/**
 * PAI-90 (RF-017, RN-17): cantidad ofrecida y vendida, precio y monto estimado.
 *
 * - Ofrecido: la mayor cantidad que tuvo la oferta según el historial (RN-15),
 *   igual que al cerrarla (PAI-150). Si no hay historial, la cantidad actual.
 * - Vendido: lo que se informó al cerrar. Si no se informó, no se inventa: la
 *   oferta suma solo a lo ofrecido.
 * - Monto estimado: vendido × precio (RN-17). Es estimado porque el precio
 *   de la oferta es estimado (RN-04).
 *
 * Se calcula en PHP: son pocas ofertas por período y así se lee más fácil
 * que con SQL y da lo mismo en MySQL y en SQLite.
 */
class ResumenVentas
{
    /**
     * @param  Collection<int, Oferta>  $ofertas  con producto e historial_max_cantidad_disponible
     */
    public function __construct(private Collection $ofertas) {}

    /** Una fila por producto, ordenadas por monto y después por lo ofrecido. */
    public function porProducto(): Collection
    {
        return $this->ofertas
            ->groupBy('producto_id')
            ->map(fn (Collection $ofertas) => [
                'producto' => $ofertas->first()->producto?->nombre ?? 'Producto eliminado',
                'unidad' => $ofertas->first()->producto?->unidad?->etiqueta() ?? '',
                ...$this->totales($ofertas),
                'precio_promedio' => round($ofertas->avg(fn (Oferta $o) => (float) $o->precio), 2),
            ])
            ->sortBy([['monto', 'desc'], ['ofrecido', 'desc']])
            ->values();
    }

    /** Totales por unidad de medida: kilos, unidades, docenas, etc. */
    public function porUnidad(): Collection
    {
        return $this->ofertas
            ->groupBy(fn (Oferta $o) => $o->producto?->unidad?->etiqueta() ?? 'Sin unidad')
            ->map(fn (Collection $ofertas, string $unidad) => ['unidad' => $unidad, ...$this->totales($ofertas)])
            ->sortKeys()
            ->values();
    }

    /** Monto estimado de todo lo vendido en el período. */
    public function montoTotal(): float
    {
        return round($this->ofertas->sum(fn (Oferta $o) => $this->monto($o) ?? 0), 2);
    }

    /** @return array{ofrecido: float, vendido: ?float, monto: ?float} */
    private function totales(Collection $ofertas): array
    {
        $conVenta = $ofertas->whereNotNull('cantidad_vendida');

        return [
            'ofrecido' => round($ofertas->sum(fn (Oferta $o) => $this->ofrecido($o)), 2),
            // null: ninguna oferta informó lo vendido (se muestra «—»).
            'vendido' => $conVenta->isEmpty() ? null : round($conVenta->sum(fn (Oferta $o) => (float) $o->cantidad_vendida), 2),
            'monto' => $conVenta->isEmpty() ? null : round($conVenta->sum(fn (Oferta $o) => $this->monto($o)), 2),
        ];
    }

    private function ofrecido(Oferta $oferta): float
    {
        return max((float) $oferta->cantidad_disponible, (float) $oferta->historial_max_cantidad_disponible);
    }

    private function monto(Oferta $oferta): ?float
    {
        return $oferta->cantidad_vendida === null ? null : (float) $oferta->cantidad_vendida * (float) $oferta->precio;
    }
}
