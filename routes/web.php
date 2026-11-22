<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Provisoria: PAI-60 cambia la vista por la del catálogo.
Route::view('/', 'welcome')->name('catalogo');

// Breeze redirige a 'dashboard' después del login, el registro y la
// verificación: acá se reparte a cada uno según su rol.
Route::get('/dashboard', function () {
    return request()->user()->esAdmin()
        ? redirect()->route('admin.inicio')
        : redirect()->route('catalogo');
})->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'rol:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Provisoria: PAI-50 la reemplaza por la vista del inicio del panel.
        Route::get('/', fn () => 'Panel del administrador')->name('inicio');
    });

require __DIR__.'/auth.php';
