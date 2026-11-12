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
use Illuminate\Support\Carbon;

/**
 * Una oferta está PUBLICADA (se ve en el catálogo) cuando:
 *  1. hoy está entre vigente_desde y vigente_hasta,
 *  2. tiene cajas disponibles,
 *  3. su feriante está validado,
 *  4. ni la oferta ni su feriante, producto o punto de retiro están dados de baja.
 */
#[Fillable(['feriante_id', 'producto_id', 'punto_retiro_id', 'precio_caja', 'cajas_disponibles', 'vigente_desde', 'vigente_hasta'])]
class Oferta extends Model
{
    /** @use HasFactory<OfertaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'ofertas';

    protected function casts(): array
    {
        return [
            'precio_caja' => 'decimal:2',
            'cajas_disponibles' => 'integer',
        ];
    }

    /*
     * Las fechas de vigencia se guardan solo como fecha (Y-m-d), sin hora.
     * El cast 'date' de Laravel guarda «2026-11-20 00:00:00», y en SQLite
     * (las pruebas) esa cadena no es igual a «2026-11-20», así que la
     * comparación con hoy fallaría. Al leerlas se obtiene un Carbon.
     */
    protected function vigenteDesde(): Attribute
    {
        return $this->fechaSinHora();
    }

    protected function vigenteHasta(): Attribute
    {
        return $this->fechaSinHora();
    }

    private function fechaSinHora(): Attribute
    {
        return Attribute::make(
            get: fn (?string $valor) => $valor === null ? null : Carbon::parse($valor)->startOfDay(),
            set: fn ($valor) => $valor === null ? null : Carbon::parse($valor)->toDateString(),
        );
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

    /** Reglas 1 y 2: vigente hoy y con stock. */
    public function scopeVigentes(Builder $query): void
    {
        // Se compara contra la fecha como texto (Y-m-d) para que se use el índice.
        $hoy = today()->toDateString();

        $query->where('vigente_desde', '<=', $hoy)
            ->where('vigente_hasta', '>=', $hoy)
            ->where('cajas_disponibles', '>', 0);
    }

    /**
     * Las 4 reglas: lo que se muestra en el catálogo.
     * whereHas() ignora los registros dados de baja (SoftDeletes).
     */
    public function scopePublicadas(Builder $query): void
    {
        $query->vigentes()
            ->whereHas('feriante', fn (Builder $q) => $q->whereNotNull('validado_at'))
            ->whereHas('producto')
            ->whereHas('puntoRetiro');
    }

    /**
     * Estado para mostrar en el panel. Usa las mismas reglas que publicadas().
     * Conviene cargar antes las relaciones con with() para evitar N+1.
     */
    protected function estado(): Attribute
    {
        return Attribute::get(function (): EstadoOferta {
            $hoy = today()->toDateString();

            if ($this->trashed() || $this->feriante === null || $this->producto === null || $this->puntoRetiro === null) {
                return EstadoOferta::DadaDeBaja;
            }

            if ($this->feriante->validado_at === null) {
                return EstadoOferta::FerianteSinValidar;
            }

            if ($this->vigente_hasta->toDateString() < $hoy) {
                return EstadoOferta::Vencida;
            }

            if ($this->vigente_desde->toDateString() > $hoy) {
                return EstadoOferta::Programada;
            }

            if ($this->cajas_disponibles <= 0) {
                return EstadoOferta::SinStock;
            }

            return EstadoOferta::Publicada;
        });
    }
}
