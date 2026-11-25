<?php

namespace App\Models;

use App\Enums\EstadoOferta;
use Database\Factories\OfertaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['feriante_id', 'producto_id', 'punto_retiro_id', 'precio', 'cantidad_disponible', 'disponible_desde', 'vigente_hasta', 'promocion', 'entrega_domicilio', 'cantidad_vendida', 'cerrada_at'])]
class Oferta extends Model
{
    /** @use HasFactory<OfertaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'ofertas';

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'cantidad_disponible' => 'decimal:2',
            'disponible_desde' => 'date',
            'vigente_hasta' => 'date',
            'promocion' => 'boolean',
            'entrega_domicilio' => 'boolean',
            'cantidad_vendida' => 'decimal:2',
            'cerrada_at' => 'datetime',
        ];
    }

    public function feriante(): BelongsTo
    {
        return $this->belongsTo(Feriante::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function puntoRetiro(): BelongsTo
    {
        return $this->belongsTo(PuntoRetiro::class);
    }

    /**
     * Ofertas vigentes: hoy está entre disponible_desde y vigente_hasta
     * (los dos días incluidos), queda cantidad disponible y no está cerrada.
     */
    public function scopeVigentes(Builder $query): void
    {
        // Se compara contra la fecha como texto (Y-m-d) y no con whereDate(),
        // para que la base pueda usar el índice de la columna.
        // "Empezó" se escribe como "antes de mañana": SQLite (el de las
        // pruebas) guarda las fechas con hora ("2026-11-20 00:00:00") y ese
        // texto es mayor que "2026-11-20", así que "<= hoy" la dejaría afuera.
        $hoy = today()->toDateString();
        $manana = today()->addDay()->toDateString();

        $query->where('disponible_desde', '<', $manana)
            ->where('vigente_hasta', '>=', $hoy)
            ->where('cantidad_disponible', '>', 0)
            ->whereNull('cerrada_at');
    }

    /**
     * Ofertas que se muestran en el catálogo: vigentes, de un feriante
     * validado y sin feriante, producto ni punto de retiro dados de baja.
     * whereHas() ya ignora los registros con borrado lógico.
     */
    public function scopePublicadas(Builder $query): void
    {
        $query->vigentes()
            ->whereHas('feriante', fn (Builder $feriante) => $feriante->whereNotNull('validado_at'))
            ->whereHas('producto')
            ->whereHas('puntoRetiro');
    }

    /**
     * Estado de la oferta para mostrar en el panel. Sigue las mismas reglas
     * que vigentes() y publicadas(): si cambia una, hay que cambiar la otra.
     */
    protected function estado(): Attribute
    {
        return Attribute::get(function (): EstadoOferta {
            $hoy = today()->toDateString();

            // Las relaciones devuelven null si el registro tiene borrado lógico.
            if ($this->trashed() || $this->feriante === null || $this->producto === null || $this->puntoRetiro === null) {
                return EstadoOferta::DadaDeBaja;
            }

            if ($this->feriante->validado_at === null) {
                return EstadoOferta::FerianteSinValidar;
            }

            if ($this->cerrada_at !== null) {
                return EstadoOferta::Cerrada;
            }

            if ($this->vigente_hasta->toDateString() < $hoy) {
                return EstadoOferta::Vencida;
            }

            if ($this->disponible_desde->toDateString() > $hoy) {
                return EstadoOferta::Programada;
            }

            if ((float) $this->cantidad_disponible <= 0) {
                return EstadoOferta::SinStock;
            }

            return EstadoOferta::Publicada;
        });
    }
}
