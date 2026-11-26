<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un clic en «Contactar por WhatsApp». Solo tiene fecha de creación y no guarda
 * el mensaje. Se borra solo a los 12 meses (model:prune, todos los días).
 */
#[Fillable(['user_id', 'feriante_id', 'oferta_id'])]
class ClicWhatsapp extends Model
{
    use Prunable;

    protected $table = 'clics_whatsapp';

    /** Solo created_at: un clic no se modifica. */
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    /** Lo que borra model:prune: los clics de más de 12 meses. */
    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subMonths(12));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function feriante(): BelongsTo
    {
        return $this->belongsTo(Feriante::class);
    }

    public function oferta(): BelongsTo
    {
        return $this->belongsTo(Oferta::class);
    }
}
