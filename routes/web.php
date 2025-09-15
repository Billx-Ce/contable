<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Persona\PersonaController;
use App\Http\Controllers\Persona\UsuarioController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Role\PermissionController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Público
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// Debe estar autenticado (y verificado)
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', fn () => Inertia::render('Dashboard'))->name('dashboard');

    // Perfil propio (nombre/email)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ================= PERSONAS =================
    Route::prefix('personas')->name('personas.')->group(function () {
        // Ver
        Route::get('/', [PersonaController::class, 'index'])
            ->middleware('perm:personas.view')->name('index');

        Route::get('/{persona}', [PersonaController::class, 'show'])
            ->whereNumber('persona')
            ->middleware('perm:personas.view')->name('show');

        // Crear
        Route::get('/create', [PersonaController::class, 'create'])
            ->middleware('perm:personas.create')->name('create');

        Route::post('/', [PersonaController::class, 'store'])
            ->middleware('perm:personas.create')->name('store');

        // Editar
        Route::get('/{persona}/edit', [PersonaController::class, 'edit'])
            ->whereNumber('persona')
            ->middleware('perm:personas.edit')->name('edit');

        Route::put('/{persona}', [PersonaController::class, 'update'])
            ->whereNumber('persona')
            ->middleware('perm:personas.edit')->name('update');

        // Borrar
        Route::delete('/{persona}', [PersonaController::class, 'destroy'])
            ->whereNumber('persona')
            ->middleware('perm:personas.delete')->name('destroy');

        // Utilidades (solo lectura)
        Route::get('/ubigeo-data', [PersonaController::class, 'getUbigeoData'])
            ->middleware('perm:personas.view')->name('ubigeo-data');

        Route::get('/provincias/{ubigeoDep}', [PersonaController::class, 'getProvincias'])
            ->whereNumber('ubigeoDep')
            ->middleware('perm:personas.view')->name('provincias');

        Route::get('/distritos/{ubigeoPro}', [PersonaController::class, 'getDistritos'])
            ->whereNumber('ubigeoPro')
            ->middleware('perm:personas.view')->name('distritos');

        Route::get('/{persona}/usuarios', [PersonaController::class, 'usuarios'])
            ->whereNumber('persona')
            ->middleware('perm:personas.view')->name('usuarios');

        Route::get('/{persona}/confirm-delete', [PersonaController::class, 'confirmDelete'])
            ->whereNumber('persona')
            ->middleware('perm:personas.delete')->name('confirm-delete');
    });

    // ================= USUARIOS =================
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        // Listado (ver usuarios)
        Route::get('/', [UsuarioController::class, 'index'])
            ->middleware('perm:usuarios.view')
            ->name('index');

        // Formulario de edición de datos (email/clave) — si lo usas
        Route::get('/{user}/edit', [UsuarioController::class, 'edit'])
            ->whereNumber('user')
            ->middleware('perm:usuarios.edit')
            ->name('edit');

        Route::put('/{user}', [UsuarioController::class, 'update'])
            ->whereNumber('user')
            ->middleware('perm:usuarios.edit')
            ->name('update');

        // 🔥 NUEVO: Actualizar solo la contraseña
        Route::put('/{user}/password', [UsuarioController::class, 'updatePassword'])
            ->whereNumber('user')
            ->middleware('perm:usuarios.edit')
            ->name('password.update');

        // 🔥 NUEVO: Pantalla para ASIGNAR ROLES
        Route::get('/{user}/roles', [UsuarioController::class, 'editRoles'])
            ->whereNumber('user')
            ->middleware('perm:roles.administrar')
            ->name('roles.edit');

        Route::put('/{user}/roles', [UsuarioController::class, 'updateRoles'])
            ->whereNumber('user')
            ->middleware('perm:roles.administrar')
            ->name('roles.update');

        // (Opcional) Eliminar usuario
        Route::delete('/{user}', [UsuarioController::class, 'destroy'])
            ->whereNumber('user')
            ->middleware('perm:usuarios.delete')
            ->name('destroy');
    });

    // ================= ROLES =================
    Route::prefix('roles')->name('roles.')->group(function () {
        Route::get('/', [RoleController::class, 'index'])
            ->middleware('perm:roles.view')->name('index');

        Route::get('/create', [RoleController::class, 'create'])
            ->middleware('perm:roles.create')->name('create');

        Route::post('/', [RoleController::class, 'store'])
            ->middleware('perm:roles.create')->name('store');

        Route::get('/{role}/edit', [RoleController::class, 'edit'])
            ->whereNumber('role')
            ->middleware('perm:roles.edit')->name('edit');

        Route::put('/{role}', [RoleController::class, 'update'])
            ->whereNumber('role')
            ->middleware('perm:roles.edit')->name('update');
    });

    // ================= PERMISOS =================
    Route::prefix('permisos')->name('permisos.')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])
            ->middleware('perm:permisos.view')->name('index');

        Route::get('/create', [PermissionController::class, 'create'])
            ->middleware('perm:permisos.create')->name('create');

        Route::post('/', [PermissionController::class, 'store'])
            ->middleware('perm:permisos.create')->name('store');

        Route::get('/{permission}/edit', [PermissionController::class, 'edit'])
            ->whereNumber('permission')
            ->middleware('perm:permisos.edit')->name('edit');

        Route::put('/{permission}', [PermissionController::class, 'update'])
            ->whereNumber('permission')
            ->middleware('perm:permisos.edit')->name('update');

        Route::delete('/{permission}', [PermissionController::class, 'destroy'])
            ->whereNumber('permission')
            ->middleware('perm:permisos.delete')->name('destroy');
    });

});

require __DIR__ . '/auth.php';
