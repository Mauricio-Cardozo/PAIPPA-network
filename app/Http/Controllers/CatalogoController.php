<?php

namespace App\Http\Controllers;

use App\Enums\OrigenConsulta;
use App\Http\Requests\CatalogoRequest;
use App\Models\Categoria;
use App\Models\ConsultaProducto;
use App\Models\Feria;
use App\Models\Oferta;
use Illuminate\View\View;

/**
 * Catálogo público (HU7): lo pueden ver los visitantes sin sesión.
 * Los filtros (HU8) son el formulario de PAI-63 y la consulta de PAI-64.
 */
class CatalogoController extends Controller
{
    /** GET /: las ofertas publicadas, filtradas y de a 12 por página. */
    public function index(CatalogoRequest $request): View
    {
        // publicadas() aplica las mismas reglas que el panel (PAI-56) y
        // filtrar() agrega los filtros elegidos (PAI-64).
        // with() trae las relaciones que usa la tarjeta en pocas consultas,
        // en lugar de una consulta por oferta (problema N+1).
        $ofertas = Oferta::publicadas()
            ->filtrar($request->validated())
            ->with(['producto.categoria', 'feriante.feria', 'puntoRetiro'])
            ->latest('updated_at')
            ->paginate(12)
            // La paginación conserva los filtros en los enlaces (?page=2&categoria=3).
            ->withQueryString();

        // PAI-89 (HU15): si buscó por texto o categoría, cuenta como consulta de
        // cada producto distinto de la primera página (como mucho 12).
        $filtros = $request->validated();
        if ((filled($filtros['q'] ?? null) || filled($filtros['categoria'] ?? null)) && $ofertas->onFirstPage()) {
            ConsultaProducto::registrar(
                $ofertas->pluck('producto_id'),
                OrigenConsulta::Busqueda,
                $request->user(),
                $request->session()->getId(),
            );
        }

        return view('catalogo.index', [
            'ofertas' => $ofertas,
            // Listas para los selectores del formulario de filtros.
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
            // Las itinerantes que ya pasaron no se ofrecen en el filtro.
            'ferias' => Feria::proximas()->orderBy('nombre')->get(['id', 'nombre']),
            // PAI-151: días y horarios para «Cómo comprar».
            'feriasHorarios' => Feria::proximas()->orderBy('nombre')->get(),
            'whatsappPaippa' => config('services.paippa.whatsapp_comercializacion') ?: null,
        ]);
    }
}
