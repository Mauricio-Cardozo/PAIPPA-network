<?php

namespace App\Models;

use App\Enums\OrigenConsulta;
use Database\Factories\ConsultaProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una consulta por producto (HU15): alguien buscó el producto en el catálogo
 * o contactó por WhatsApp por una oferta de ese producto. Solo tiene fecha de
 * creación: una consulta no se modifica.
 */
#[Fillable(['producto_id', 'user_id', 'visitante', 'origen', 'fecha'])]
class ConsultaProducto extends Model
{
    /** @use HasFactory<ConsultaProductoFactory> */
    use HasFactory;

    protected $table = 'consultas_producto';

    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'origen' => OrigenConsulta::class,
            'fecha' => 'date',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Cómo se identifica a quien consulta, sin guardar datos de más:
     * «user:{id}» si inició sesión, o el hash de la sesión si es invitado.
     * Nunca se guarda el id de sesión crudo (Ley 25.326).
     */
    public static function visitante(?User $user, string $idSesion): string
    {
        return $user !== null ? 'user:'.$user->id : hash('sha256', $idSesion);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
