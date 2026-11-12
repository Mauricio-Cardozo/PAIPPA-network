<?php

use App\Http\Controllers\Admin\CategoriaController;
use App\Http\Controllers\Admin\EstadisticaController;
use App\Http\Controllers\Admin\FeriaController;
use App\Http\Controllers\Admin\FerianteController;
use App\Http\Controllers\Admin\InicioController;
use App\Http\Controllers\Admin\OfertaController;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\PuntoRetiroController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\CatalogoController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\FerianteController as PerfilFerianteController;
use App\Http\Controllers\MapaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Rutas públicas (visitantes y usuarios)
|--------------------------------------------------------------------------
*/

Route::get('/', [CatalogoController::class, 'index'])->name('catalogo');
Route::get('/feriantes/{feriante}', [PerfilFerianteController::class, 'show'])->whereNumber('feriante')->name('feriantes.show');
Route::get('/mapa', [MapaController::class, 'index'])->name('mapa');
Route::get('/mapa/puntos', [MapaController::class, 'puntos'])->name('mapa.puntos');

/*
|--------------------------------------------------------------------------
| 2. Rutas con sesión iniciada
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'sin-cache'])->group(function () {
    // Breeze redirige a 'dashboard' después del login, el registro y la
    // verificación: acá se reparte a cada uno según su rol.
    Route::get('/dashboard', function () {
        return request()->user()->esAdmin()
            ? redirect()->route('admin.inicio')
            : redirect()->route('catalogo');
    })->name('dashboard');

    // Contacto por WhatsApp: registra el clic y redirige a wa.me.
    Route::get('/contacto/whatsapp/{oferta}', [ContactoController::class, 'oferta'])
        ->whereNumber('oferta')
        ->middleware('throttle:30,1')
        ->name('contacto.whatsapp');
    Route::get('/contacto/whatsapp/feriante/{feriante}', [ContactoController::class, 'feriante'])
        ->whereNumber('feriante')
        ->middleware('throttle:30,1')
        ->name('contacto.whatsapp.feriante');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| 3. Panel del administrador
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'rol:admin', 'sin-cache'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', InicioController::class)->name('inicio');

        // ABM: index, create, store, edit, update y destroy (sin show).
        Route::resource('categorias', CategoriaController::class)->except('show');
        Route::resource('productos', ProductoController::class)->except('show');
        Route::resource('ferias', FeriaController::class)->except('show');
        Route::resource('puntos-retiro', PuntoRetiroController::class)
            ->except('show')
            ->parameters(['puntos-retiro' => 'puntoRetiro']);

        Route::resource('feriantes', FerianteController::class)->except('show');
        Route::patch('feriantes/{feriante}/validar', [FerianteController::class, 'validar'])->name('feriantes.validar');
        Route::patch('feriantes/{feriante}/invalidar', [FerianteController::class, 'invalidar'])->name('feriantes.invalidar');

        Route::resource('ofertas', OfertaController::class)->except('show');
        Route::patch('ofertas/{oferta}/stock', [OfertaController::class, 'stock'])->name('ofertas.stock');

        Route::get('estadisticas', [EstadisticaController::class, 'index'])->name('estadisticas');

        Route::get('usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::patch('usuarios/{usuario}/bloquear', [UsuarioController::class, 'bloquear'])->name('usuarios.bloquear');
        Route::patch('usuarios/{usuario}/desbloquear', [UsuarioController::class, 'desbloquear'])->name('usuarios.desbloquear');
    });

require __DIR__.'/auth.php';
