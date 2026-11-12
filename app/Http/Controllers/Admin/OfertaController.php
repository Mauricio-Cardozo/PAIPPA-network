<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OfertaRequest;
use App\Http\Requests\Admin\StockOfertaRequest;
use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * ABM de ofertas. El admin carga la oferta de cada feriante con los datos
 * que le pasa el feriante (precio estimado, cajas y vigencia).
 */
class OfertaController extends Controller
{
    public function index(Request $request): View
    {
        $ofertas = Oferta::query()
            // Las relaciones se cargan juntas: el estado las usa y así no hay N+1.
            ->with(['feriante', 'producto', 'puntoRetiro'])
            ->when($request->filled('buscar'), fn ($q) => $q->whereHas('producto', fn ($p) => $p->where('nombre', 'like', '%'.$request->input('buscar').'%')))
            ->when($request->filled('feriante'), fn ($q) => $q->where('feriante_id', $request->integer('feriante')))
            ->latest('updated_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.ofertas.index', [
            'ofertas' => $ofertas,
            'feriantes' => Feriante::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function create(): View
    {
        return view('admin.ofertas.form', $this->datosFormulario(new Oferta([
            'vigente_desde' => today(),
            'vigente_hasta' => today()->addDays(6),
        ])));
    }

    public function store(OfertaRequest $request): RedirectResponse
    {
        Oferta::create($request->validated());

        return redirect()->route('admin.ofertas.index')->with('exito', 'Oferta creada.');
    }

    public function edit(Oferta $oferta): View
    {
        return view('admin.ofertas.form', $this->datosFormulario($oferta));
    }

    public function update(OfertaRequest $request, Oferta $oferta): RedirectResponse
    {
        $oferta->update($request->validated());

        return redirect()->route('admin.ofertas.index')->with('exito', 'Oferta actualizada.');
    }

    public function destroy(Oferta $oferta): RedirectResponse
    {
        $oferta->delete();

        return redirect()->route('admin.ofertas.index')->with('exito', 'Oferta dada de baja.');
    }

    /** Acción rápida: cambia las cajas y la fecha «hasta» sin abrir el formulario. */
    public function stock(StockOfertaRequest $request, Oferta $oferta): RedirectResponse
    {
        $oferta->update($request->validated());

        return back()->with('exito', 'Stock y vigencia actualizados.');
    }

    /** Listas para los selectores: solo feriantes validados y puntos agrupados por feria. */
    private function datosFormulario(Oferta $oferta): array
    {
        return [
            'oferta' => $oferta,
            'feriantes' => Feriante::validados()->with('feria')->orderBy('nombre')->get(),
            'productos' => Producto::with('categoria')->orderBy('nombre')->get(),
            'ferias' => Feria::with(['puntosRetiro' => fn ($q) => $q->orderBy('nombre')])->orderBy('nombre')->get(),
        ];
    }
}
