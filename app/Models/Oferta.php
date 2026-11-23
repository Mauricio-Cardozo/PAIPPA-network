<?php

namespace App\Models;

use Database\Factories\OfertaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
            'vigente_desde' => 'date',
            'vigente_hasta' => 'date',
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
