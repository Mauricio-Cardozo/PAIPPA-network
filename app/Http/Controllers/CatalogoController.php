<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogoRequest;
use App\Models\Categoria;
use App\Models\Feria;
use App\Models\Oferta;
use Illuminate\View\View;

/**
 * Catálogo público: las ofertas publicadas, con búsqueda y filtros.
 */
class CatalogoController extends Controller
{
    public function index(CatalogoRequest $request): View
    {
        $filtros = $request->filtros();

        $ofertas = Oferta::publicadas()
            ->filtrar($filtros)
            // Eager loading: todo lo que muestra la tarjeta, en pocas consultas (sin N+1).
            ->with(['producto.categoria', 'feriante.feria', 'puntoRetiro'])
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString();

        return view('catalogo.index', [
            'ofertas' => $ofertas,
            'filtros' => $filtros,
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
            'ferias' => Feria::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }
}
