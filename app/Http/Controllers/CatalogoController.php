<?php

namespace App\Http\Controllers;

use App\Models\Oferta;
use Illuminate\View\View;

/**
 * Catálogo público (HU7): lo pueden ver los visitantes sin sesión.
 * Los filtros se agregan en PAI-63 y PAI-64.
 */
class CatalogoController extends Controller
{
    /** GET /: las ofertas publicadas, de a 12 por página. */
    public function index(): View
    {
        // publicadas() aplica las mismas reglas que el panel (PAI-56).
        // with() trae las relaciones que usa la tarjeta en pocas consultas,
        // en lugar de una consulta por oferta (problema N+1).
        $ofertas = Oferta::publicadas()
            ->with(['producto.categoria', 'feriante.feria', 'puntoRetiro'])
            ->latest('updated_at')
            ->paginate(12);

        return view('catalogo.index', compact('ofertas'));
    }
}
