<?php

namespace App\Models;

use Database\Factories\FeriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nombre', 'descripcion', 'dias_horario'])]
class Feria extends Model
{
    /** @use HasFactory<FeriaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'ferias';

    public function puntosRetiro(): HasMany
    {
        return $this->hasMany(PuntoRetiro::class);
    }

    public function feriantes(): HasMany
    {
        return $this->hasMany(Feriante::class);
    }
}
