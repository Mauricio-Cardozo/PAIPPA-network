<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'cuerpo'])]
class Mensaje extends Model
{
    protected $table = 'mensajes';

    protected function casts(): array
    {
        return [
            'leido_at' => 'datetime',
        ];
    }

    public function conversacion(): BelongsTo
    {
        return $this->belongsTo(Conversacion::class);
    }

    public function remitente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
