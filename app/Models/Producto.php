<?php

namespace App\Models;

use App\Enums\Unidad;
use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['categoria_id', 'nombre', 'unidad', 'descripcion'])]
class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected function casts(): array
    {
        return [
            'unidad' => Unidad::class,
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ofertas(): HasMany
    {
        return $this->hasMany(Oferta::class);
    }

    /** Los precios tope semanales del PAIPPA para este producto (RN-16). */
    public function preciosReferencia(): HasMany
    {
        return $this->hasMany(PrecioReferencia::class);
    }

    /**
     * El precio tope vigente: el de la semana más reciente que ya empezó.
     * Se compara con «antes de mañana» por cómo guarda las fechas SQLite.
     */
    public function precioTopeVigente(): HasOne
    {
        return $this->hasOne(PrecioReferencia::class)->ofMany(
            ['semana' => 'max'],
            fn ($query) => $query->where('semana', '<', today()->addDay()->toDateString()),
        );
    }
}
