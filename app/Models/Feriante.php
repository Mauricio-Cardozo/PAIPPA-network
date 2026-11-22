<?php

namespace App\Models;

use Database\Factories\FerianteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/*
 * validado_at NO es fillable: solo el admin valida, con una acción propia.
 */
#[Fillable(['feria_id', 'nombre', 'telefono_whatsapp', 'descripcion'])]
class Feriante extends Model
{
    /** @use HasFactory<FerianteFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'feriantes';

    protected function casts(): array
    {
        return [
            'validado_at' => 'datetime',
        ];
    }

    public function feria(): BelongsTo
    {
        return $this->belongsTo(Feria::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ofertas(): HasMany
    {
        return $this->hasMany(Oferta::class);
    }

    public function estaValidado(): bool
    {
        return $this->validado_at !== null;
    }

    /** Solo los feriantes validados por el administrador. */
    public function scopeValidados(Builder $query): void
    {
        $query->whereNotNull('validado_at');
    }
}
