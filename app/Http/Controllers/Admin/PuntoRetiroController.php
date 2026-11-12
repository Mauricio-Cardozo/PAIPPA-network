<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PuntoRetiroRequest;
use App\Models\Feria;
use App\Models\PuntoRetiro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** ABM de puntos de retiro (misma estructura que CategoriaController). */
class PuntoRetiroController extends Controller
{
    public function index(Request $request): View
    {
        $puntos = PuntoRetiro::query()
            ->with('feria')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->input('buscar').'%'))
            ->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return view('admin.puntos-retiro.index', compact('puntos'));
    }

    public function create(): View
    {
        return view('admin.puntos-retiro.form', [
            'punto' => new PuntoRetiro,
            'ferias' => Feria::orderBy('nombre')->get(),
        ]);
    }

    public function store(PuntoRetiroRequest $request): RedirectResponse
    {
        PuntoRetiro::create($request->validated());

        return redirect()->route('admin.puntos-retiro.index')->with('exito', 'Punto de retiro creado.');
    }

    public function edit(PuntoRetiro $puntoRetiro): View
    {
        return view('admin.puntos-retiro.form', [
            'punto' => $puntoRetiro,
            'ferias' => Feria::orderBy('nombre')->get(),
        ]);
    }

    public function update(PuntoRetiroRequest $request, PuntoRetiro $puntoRetiro): RedirectResponse
    {
        $puntoRetiro->update($request->validated());

        return redirect()->route('admin.puntos-retiro.index')->with('exito', 'Punto de retiro actualizado.');
    }

    /** Baja lógica: no se permite si tiene ofertas cargadas. */
    public function destroy(PuntoRetiro $puntoRetiro): RedirectResponse
    {
        if ($puntoRetiro->ofertas()->exists()) {
            return back()->with('error', 'No se puede dar de baja: el punto de retiro tiene ofertas cargadas.');
        }

        $puntoRetiro->delete();

        return redirect()->route('admin.puntos-retiro.index')->with('exito', 'Punto de retiro dado de baja.');
    }
}
