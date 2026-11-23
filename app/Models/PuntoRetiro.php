<?php

namespace App\Models;

use Database\Factories\PuntoRetiroFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['feria_id', 'nombre', 'direccion', 'horario', 'latitud', 'longitud'])]
class PuntoRetiro extends Model
{
    /** @use HasFactory<PuntoRetiroFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'puntos_retiro';

    protected function casts(): array
    {
        return [
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
        ];
    }

    public function feria(): BelongsTo
    {
        return $this->belongsTo(Feria::class);
    }

    public function ofertas(): HasMany
    {
        return $this->hasMany(Oferta::class);
    }
}
