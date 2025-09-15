<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Role\PermissionController;

// (opcional) endpoint para obtener el usuario autenticado
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- Rutas protegidas con Sanctum ---
Route::middleware(['auth:sanctum'])->group(function () {

    // ROLES
    Route::apiResource('roles', RoleController::class);

    // PERMISOS
    Route::apiResource('permissions', PermissionController::class)->except(['update']);
    Route::patch('permissions/{permission}', [PermissionController::class, 'update']);

    // RELACIÓN ROL <-> PERMISOS
    Route::post('roles/{role}/permissions/sync',   [RoleController::class, 'syncPermissions']);
    Route::post('roles/{role}/permissions/attach', [RoleController::class, 'attachPermissions']);
    Route::post('roles/{role}/permissions/detach', [RoleController::class, 'detachPermissions']);

    // UTILIDADES
    Route::get('permissions-grouped', [PermissionController::class, 'grouped']);
    Route::post('permissions-crud',   [PermissionController::class, 'createCrud']);
});
