<?php

namespace App\Models;

use Database\Factories\ProductoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['categoria_id', 'nombre', 'unidad', 'descripcion'])]
class Producto extends Model
{
    /** @use HasFactory<ProductoFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function ofertas(): HasMany
    {
        return $this->hasMany(Oferta::class);
    }
}
