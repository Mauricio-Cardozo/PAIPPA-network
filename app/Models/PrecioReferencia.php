<?php

namespace App\Models;

use Database\Factories\PrecioReferenciaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Precio tope que sugiere el PAIPPA para un producto en una semana (RN-16).
 * cargado_por no es fillable: lo asigna el código con el admin que lo carga.
 */
#[Fillable(['producto_id', 'precio_tope', 'semana'])]
class PrecioReferencia extends Model
{
    /** @use HasFactory<PrecioReferenciaFactory> */
    use HasFactory;

    protected $table = 'precios_referencia';

    protected function casts(): array
    {
        return [
            'precio_tope' => 'decimal:2',
            'semana' => 'date',
        ];
    }

    /** El lunes de la semana de una fecha: así se guarda la columna semana. */
    public static function inicioDeSemana(Carbon|string $fecha): string
    {
        return Carbon::parse($fecha)->startOfWeek(Carbon::MONDAY)->toDateString();
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function cargadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cargado_por');
    }
}
