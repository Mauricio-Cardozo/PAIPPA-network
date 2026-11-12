<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lo que recibe el mapa por cada punto de retiro (JSON).
 * Solo datos públicos: nada de teléfonos.
 */
class PuntoRetiroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'horario' => $this->horario,
            'latitud' => (float) $this->latitud,
            'longitud' => (float) $this->longitud,
            'feria' => [
                'id' => $this->feria->id,
                'nombre' => $this->feria->nombre,
            ],
            'ofertas_publicadas' => $this->ofertas_publicadas_count,
            'url_catalogo' => route('catalogo', ['feria' => $this->feria->id]),
        ];
    }
}
