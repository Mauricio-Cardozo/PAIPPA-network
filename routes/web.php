
<?php

use App\Http\Controllers\ProfileController;
use App\Models\Categoria;
use App\Models\Feriante;
use App\Models\Feria;
use App\Models\Oferta;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Rutas públicas
|--------------------------------------------------------------------------
*/

Route::view('/', 'welcome')->name('catalogo');

/*
|--------------------------------------------------------------------------
| 2. Rutas con sesión iniciada
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'sin-cache'])->group(function () {

    Route::get('/dashboard', function () {
        return request()->user()->esAdmin()
            ? redirect()->route('admin.inicio')
            : redirect()->route('catalogo');
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 3. Panel del administrador - PAI-50
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin', 'sin-cache'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/', function () {

            // Cantidades reales de la base de datos
            $totalFeriantes = Feriante::count();

            $totalFerias = Feria::count();

            $totalUsuarios = User::count();

            // Ofertas con fechas vigentes y stock disponible
            $totalOfertasVigentes = Oferta::query()
                ->whereDate('vigente_desde', '<=', today())
                ->whereDate('vigente_hasta', '>=', today())
                ->where('cajas_disponibles', '>', 0)
                ->count();

            return view('admin.inicio', compact(
                'totalFeriantes',
                'totalFerias',
                'totalUsuarios',
                'totalOfertasVigentes'
            ));

        })->name('inicio');

    });

require __DIR__.'/auth.php';
