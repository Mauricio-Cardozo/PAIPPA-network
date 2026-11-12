<?php

namespace App\Models;

use Database\Factories\CategoriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nombre'])]
class Categoria extends Model
{
    /** @use HasFactory<CategoriaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'categorias';

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
