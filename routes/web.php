<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Persona\PersonaController;
use App\Http\Controllers\Persona\UsuarioController; // asegúrate de que este namespace es el correcto
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Ruta de inicio público
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'      => Route::has('login'),
        'canRegister'   => Route::has('register'),
        'laravelVersion'=> Application::VERSION,
        'phpVersion'    => PHP_VERSION,
    ]);
});

// Rutas de autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ================== PERSONAS ==================
    Route::prefix('personas')->name('personas.')->group(function () {
        // Rutas principales del CRUD (sin parámetros)
        Route::get('/', [PersonaController::class, 'index'])->name('index');
        Route::get('/create', [PersonaController::class, 'create'])->name('create');
        Route::post('/', [PersonaController::class, 'store'])->name('store');

        // APIs de UBIGEO (van antes de las rutas con parámetros)
        Route::get('/ubigeo-data', [PersonaController::class, 'getUbigeoData'])->name('ubigeo-data');
        Route::get('/provincias/{ubigeoDep}', [PersonaController::class, 'getProvincias'])
            ->whereNumber('ubigeoDep')->name('provincias');
        Route::get('/distritos/{ubigeoPro}', [PersonaController::class, 'getDistritos'])
            ->whereNumber('ubigeoPro')->name('distritos');

        // Relación Persona → Usuarios (página dedicada)
        Route::get('/{persona}/usuarios', [PersonaController::class, 'usuarios'])
            ->whereNumber('persona')->name('usuarios');

        // Confirmación antes de eliminar (opcional)
        Route::get('/{persona}/confirm-delete', [PersonaController::class, 'confirmDelete'])
            ->whereNumber('persona')->name('confirm-delete');

        // Rutas con parámetros (al final y solo números)
        Route::get('/{persona}', [PersonaController::class, 'show'])
            ->whereNumber('persona')->name('show');
        Route::get('/{persona}/edit', [PersonaController::class, 'edit'])
            ->whereNumber('persona')->name('edit');
        Route::put('/{persona}', [PersonaController::class, 'update'])
            ->whereNumber('persona')->name('update');
        Route::delete('/{persona}', [PersonaController::class, 'destroy'])
            ->whereNumber('persona')->name('destroy');
    });

    // ================== USUARIOS (listado independiente) ==================
    // Importante: fuera del prefijo 'personas' para que sea /usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
});

// Rutas de autenticación (Breeze/Jetstream)
require __DIR__.'/auth.php';
