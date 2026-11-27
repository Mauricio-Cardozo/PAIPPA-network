<?php

namespace App\Models;

use Database\Factories\MedioPagoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Un medio de pago que aceptan los feriantes: efectivo, transferencia... (RF-006). */
#[Fillable(['nombre'])]
class MedioPago extends Model
{
    /** @use HasFactory<MedioPagoFactory> */
    use HasFactory;

    protected $table = 'medios_pago';

    public function feriantes(): BelongsToMany
    {
        return $this->belongsToMany(Feriante::class, 'feriante_medio_pago');
    }
}
