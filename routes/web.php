<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\FeriaController;
use App\Http\Controllers\Admin\FerianteController;
use App\Http\Controllers\Admin\InicioController;
use App\Http\Controllers\Admin\OfertaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\PuntoRetiroController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\PerfilFerianteController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Rutas públicas
|--------------------------------------------------------------------------
*/

// catálogo público con las ofertas publicadas
Route::get('/', [CatalogoController::class, 'index'])->name('catalogo');

// puntos de retiro en JSON para el mapa (pública, sin datos personales)
Route::get('/mapa/puntos', [MapaController::class, 'puntos'])->name('mapa.puntos');

// vista pública del mapa con Leaflet y OpenStreetMap.
Route::view('/mapa', 'mapa')->name('mapa');

// guarda el contacto como destino y envía al visitante al login.
Route::get('/login/contacto/{oferta}', function (Oferta $oferta) {
    session()->put(
        'url.intended',
        route('contacto.whatsapp', $oferta)
    );

    return redirect()->route('login');
})
    ->middleware('guest')
    ->name('contacto.login');

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

    //  registra el clic y redirige a WhatsApp con el mensaje armado (PAI-74)
    Route::get('/contacto/whatsapp/{oferta}', [ContactoController::class, 'whatsapp'])
        ->name('contacto.whatsapp');
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

        // PAI-140: contadores y accesos a los ABM
        Route::get('/', [InicioController::class, 'index'])->name('inicio');

        //  ABM de categorías y productos (sin la página show)
        Route::resource('categorias', CategoriaController::class)->except('show');
        Route::resource('productos', ProductoController::class)->except('show');

        //  ABM de ferias y puntos de retiro (sin la página show)
        Route::resource('ferias', FeriaController::class)->except('show');
        Route::resource('puntos-retiro', PuntoRetiroController::class)
            ->except('show')
            ->parameters(['puntos-retiro' => 'puntoRetiro']);

        //  ABM de feriantes, con validar e invalidar como acciones aparte
        Route::resource('feriantes', FerianteController::class)->except('show');
        Route::patch('feriantes/{feriante}/validar', [FerianteController::class, 'validar'])->name('feriantes.validar');
        Route::patch('feriantes/{feriante}/invalidar', [FerianteController::class, 'invalidar'])->name('feriantes.invalidar');

        // carga de ofertas y actualización rápida de cantidad y vigencia
        Route::resource('ofertas', OfertaController::class)->except('show');
        Route::patch('ofertas/{oferta}/stock', [OfertaController::class, 'stock'])->name('ofertas.stock');
    });

// perfil público del feriante (solo validados)
Route::get('/feriantes/{feriante}', [PerfilFerianteController::class, 'show'])->name('feriantes.show');

require __DIR__.'/auth.php';
