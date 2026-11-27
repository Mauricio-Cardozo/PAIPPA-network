<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\EstadisticasRequest;
use App\Models\ClicWhatsapp;
use App\Models\ConsultaProducto;
use App\Models\Oferta;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * PAI-90 (HU15): panel de indicadores con filtro de fechas.
 * Las consultas son las mismas en MySQL y en SQLite (las pruebas): solo
 * COUNT y GROUP BY, sin funciones de fecha propias de un motor.
 */
class EstadisticaController extends Controller
{
    public function index(EstadisticasRequest $request): View
    {
        // Por defecto, los últimos 30 días contando hoy.
        $desde = Carbon::parse($request->validated('desde') ?? today()->subDays(29));
        $hasta = Carbon::parse($request->validated('hasta') ?? today());

        // Se compara «desde ≤ fecha < el día después de hasta», así entra todo
        // el último día sin importar la hora.
        $inicio = $desde->copy()->startOfDay();
        $fin = $hasta->copy()->addDay()->startOfDay();

        // Productos más consultados (búsquedas y clics de WhatsApp, PAI-89).
        $productos = ConsultaProducto::query()
            ->selectRaw('producto_id, COUNT(*) as total')
            ->where('fecha', '>=', $inicio->toDateString())
            ->where('fecha', '<', $fin->toDateString())
            ->groupBy('producto_id')
            ->orderByDesc('total')
            ->limit(10)
            // withTrashed: un producto dado de baja igual muestra su nombre.
            ->with(['producto' => fn ($consulta) => $consulta->withTrashed()])
            ->get();

        // Clics de WhatsApp por feriante (PAI-75).
        $feriantes = ClicWhatsapp::query()
            ->selectRaw('feriante_id, COUNT(*) as total')
            ->where('created_at', '>=', $inicio)
            ->where('created_at', '<', $fin)
            ->groupBy('feriante_id')
            ->orderByDesc('total')
            ->limit(10)
            ->with(['feriante' => fn ($consulta) => $consulta->withTrashed()])
            ->get();

        return view('admin.estadisticas.index', [
            'desde' => $desde,
            'hasta' => $hasta,
            'productos' => $productos,
            'feriantes' => $feriantes,
            // Ofertas que estuvieron disponibles en algún momento del período.
            'totalOfertas' => Oferta::where('disponible_desde', '<', $fin->toDateString())
                ->where('vigente_hasta', '>=', $inicio->toDateString())
                ->count(),
            'totalClics' => ClicWhatsapp::where('created_at', '>=', $inicio)->where('created_at', '<', $fin)->count(),
            'usuariosNuevos' => User::where('created_at', '>=', $inicio)->where('created_at', '<', $fin)->count(),
        ]);
    }
}
