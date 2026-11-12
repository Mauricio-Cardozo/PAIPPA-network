<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FeriaRequest;
use App\Models\Feria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** ABM de ferias (misma estructura que CategoriaController). */
class FeriaController extends Controller
{
    public function index(Request $request): View
    {
        $ferias = Feria::query()
            ->withCount(['puntosRetiro', 'feriantes'])
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->input('buscar').'%'))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.ferias.index', compact('ferias'));
    }

    public function create(): View
    {
        return view('admin.ferias.form', ['feria' => new Feria]);
    }

    public function store(FeriaRequest $request): RedirectResponse
    {
        Feria::create($request->validated());

        return redirect()->route('admin.ferias.index')->with('exito', 'Feria creada.');
    }

    public function edit(Feria $feria): View
    {
        return view('admin.ferias.form', compact('feria'));
    }

    public function update(FeriaRequest $request, Feria $feria): RedirectResponse
    {
        $feria->update($request->validated());

        return redirect()->route('admin.ferias.index')->with('exito', 'Feria actualizada.');
    }

    /** Baja lógica: no se permite si tiene puntos de retiro o feriantes activos. */
    public function destroy(Feria $feria): RedirectResponse
    {
        if ($feria->puntosRetiro()->exists() || $feria->feriantes()->exists()) {
            return back()->with('error', 'No se puede dar de baja: la feria tiene puntos de retiro o feriantes activos.');
        }

        $feria->delete();

        return redirect()->route('admin.ferias.index')->with('exito', 'Feria dada de baja.');
    }
}
