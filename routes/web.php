<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| 1. Rutas públicas (visitantes y usuarios)
|--------------------------------------------------------------------------
*/

Route::view('/', 'catalogo.index')->name('catalogo');

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
        Route::view('/', 'admin.inicio')->name('inicio');
    });

require __DIR__.'/auth.php';
