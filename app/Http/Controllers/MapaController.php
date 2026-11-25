<?php

namespace App\Http\Controllers;

use App\Http\Resources\PuntoRetiroResource;
use App\Models\PuntoRetiro;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Datos para el mapa de puntos de retiro (HU10).
 * La página del mapa con Leaflet la arma la PAI-71; acá solo está el JSON.
 */
class MapaController extends Controller
{
    /** GET /mapa/puntos: los puntos de retiro activos en JSON. */
    public function puntos(): AnonymousResourceCollection
    {
        // whereHas('feria') deja afuera los puntos de una feria dada de baja.
        $puntos = PuntoRetiro::query()
            ->whereHas('feria')
            ->with('feria')
            // Cuántas ofertas publicadas tiene cada punto (mismo scope que el catálogo).
            ->withCount(['ofertas as ofertas_publicadas_count' => fn ($q) => $q->publicadas()])
            ->orderBy('nombre')
            ->get();

        return PuntoRetiroResource::collection($puntos);
    }
}
