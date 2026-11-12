<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Conversación entre un revendedor y un feriante (HU12).
 * Solo la inicia el revendedor; el feriante responde desde su cuenta.
 */
#[Fillable(['revendedor_id', 'feriante_id'])]
class Conversacion extends Model
{
    protected $table = 'conversaciones';

    public function revendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revendedor_id');
    }

    public function feriante(): BelongsTo
    {
        return $this->belongsTo(Feriante::class);
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(Mensaje::class);
    }

    public function ultimoMensaje(): HasOne
    {
        return $this->hasOne(Mensaje::class)->latestOfMany();
    }

    /** ¿El usuario es uno de los dos participantes? */
    public function participa(User $user): bool
    {
        return $this->revendedor_id === $user->id
            || ($this->feriante !== null && $this->feriante->user_id === $user->id);
    }

    /** Las conversaciones donde participa el usuario (como revendedor o como feriante). */
    public function scopeDe(Builder $query, User $user): void
    {
        $query->where(fn (Builder $q) => $q
            ->where('revendedor_id', $user->id)
            ->orWhereHas('feriante', fn (Builder $f) => $f->where('user_id', $user->id)));
    }
}
