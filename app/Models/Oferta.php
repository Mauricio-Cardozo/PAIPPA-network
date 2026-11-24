<?php

namespace App\Models;

use Database\Factories\OfertaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
}
