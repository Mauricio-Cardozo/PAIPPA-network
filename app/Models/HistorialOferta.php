<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un alta o un cambio de precio, cantidad o vigencia de una oferta (RN-15).
 * Lo registra el modelo Oferta al guardarse; no se carga a mano.
 */
#[Fillable(['oferta_id', 'user_id', 'accion', 'precio', 'cantidad_disponible', 'vigente_hasta'])]
class HistorialOferta extends Model
{
    protected $table = 'historial_ofertas';

    /** Solo created_at: un registro del historial no se modifica. */
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'cantidad_disponible' => 'decimal:2',
            'vigente_hasta' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }

    /** Quién hizo el cambio. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
