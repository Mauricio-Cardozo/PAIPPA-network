<?php

namespace App\Http\Controllers;

use App\Http\Resources\PuntoRetiroResource;
use App\Models\PuntoRetiro;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\View\View;

/**
 * Mapa de puntos de retiro (Leaflet + OpenStreetMap, sin clave de API).
 */
class MapaController extends Controller
{
    /** La página con el mapa. Los puntos los pide el JavaScript a puntos(). */
    public function index(): View
    {
        return view('mapa.index');
    }

    /** GET /mapa/puntos: los puntos de retiro activos en JSON. */
    public function puntos(): AnonymousResourceCollection
    {
        // whereHas('feria') deja afuera los puntos de una feria dada de baja.
        $puntos = PuntoRetiro::query()
            ->whereHas('feria')
            ->with('feria')
            ->withCount(['ofertas as ofertas_publicadas_count' => fn ($q) => $q->publicadas()])
            ->orderBy('nombre')
            ->get();

        return PuntoRetiroResource::collection($puntos);
    }
}
