<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Rol;
use App\Http\Controllers\Controller;
use App\Models\ClicWhatsapp;
use App\Models\ConsultaProducto;
use App\Models\Feriante;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * Panel de indicadores (HU15) con un rango de fechas.
 * Las consultas usan solo SQL estándar (sin DATE_FORMAT ni IF), para que
 * funcionen igual en MySQL y en SQLite (las pruebas).
 */
class EstadisticaController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ]);

        // Por defecto, los últimos 30 días.
        $desde = Carbon::parse($request->input('desde', today()->subDays(29)))->startOfDay();
        $hasta = Carbon::parse($request->input('hasta', today()))->endOfDay();

        $productosMasConsultados = ConsultaProducto::query()
            ->selectRaw('producto_id, COUNT(*) as total')
            ->whereBetween('fecha', [$desde->toDateString(), $hasta->toDateString()])
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('producto:id,nombre')
            ->get();

        $clicsPorFeriante = ClicWhatsapp::query()
            ->selectRaw('feriante_id, COUNT(*) as total')
            ->whereBetween('created_at', [$desde, $hasta])
            ->groupBy('feriante_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with('feriante:id,nombre')
            ->get();

        $indicadores = [
            'Feriantes activos' => Feriante::validados()->count(),
            'Ofertas publicadas hoy' => Oferta::publicadas()->count(),
            'Revendedores registrados' => User::where('rol', Rol::Revendedor)->count(),
            'Usuarios nuevos en el período' => User::whereBetween('created_at', [$desde, $hasta])->count(),
            'Clics en WhatsApp en el período' => ClicWhatsapp::whereBetween('created_at', [$desde, $hasta])->count(),
        ];

        return view('admin.estadisticas', [
            'desde' => $desde,
            'hasta' => $hasta,
            'indicadores' => $indicadores,
            'productosMasConsultados' => $productosMasConsultados,
            'clicsPorFeriante' => $clicsPorFeriante,
        ]);
    }
}
