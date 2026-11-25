<?php

namespace App\Models;

use App\Enums\TipoFeria;
use Database\Factories\FeriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'nombre', 'tipo', 'direccion', 'barrio', 'dias', 'fecha',
    'hora_inicio', 'hora_fin', 'latitud', 'longitud', 'descripcion',
])]
class Feria extends Model
{
    /** @use HasFactory<FeriaFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'ferias';

    protected function casts(): array
    {
        return [
            'tipo' => TipoFeria::class,
            'fecha' => 'date',
            'latitud' => 'decimal:7',
            'longitud' => 'decimal:7',
        ];
    }

    public function puntosRetiro(): HasMany
    {
        return $this->hasMany(PuntoRetiro::class);
    }

    public function feriantes(): HasMany
    {
        return $this->hasMany(Feriante::class);
    }

    /**
     * Cuándo se hace la feria, para mostrar en las pantallas:
     * «Miércoles y sábados · 6:00 a 12:00» o «Sábado 29/11 · 7:00 a 12:00».
     */
    protected function cuando(): Attribute
    {
        return Attribute::get(function (): string {
            $dia = $this->tipo === TipoFeria::Itinerante
                ? ucfirst((string) $this->fecha?->translatedFormat('l d/m'))
                : (string) $this->dias;

            if ($this->hora_inicio && $this->hora_fin) {
                $dia .= ' · '.substr($this->hora_inicio, 0, 5).' a '.substr($this->hora_fin, 0, 5);
            }

            return $dia;
        });
    }
}
