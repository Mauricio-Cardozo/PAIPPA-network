<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\FeriaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\PuntoRetiroController;
use App\Http\Controllers\ProfileController;
use App\Models\Feria;
use App\Models\Feriante;
use App\Models\Oferta;
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

            // Ofertas vigentes: misma regla que el scope Oferta::vigentes()
            $totalOfertasVigentes = Oferta::vigentes()->count();

            return view('admin.inicio', compact(
                'totalFeriantes',
                'totalFerias',
                'totalUsuarios',
                'totalOfertasVigentes'
            ));
        })->name('inicio');

        // PAI-54: ABM de categorías y productos (sin la página show)
        Route::resource('categorias', CategoriaController::class)->except('show');
        Route::resource('productos', ProductoController::class)->except('show');

        // PAI-48: ABM de ferias y puntos de retiro (sin la página show)
        Route::resource('ferias', FeriaController::class)->except('show');
        Route::resource('puntos-retiro', PuntoRetiroController::class)
            ->except('show')
            ->parameters(['puntos-retiro' => 'puntoRetiro']);
    });

require __DIR__.'/auth.php';
