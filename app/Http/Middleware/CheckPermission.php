<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    // Usar: ->middleware('perm:personas.create')
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user || !method_exists($user, 'hasPermission')) {
            abort(403, 'No autorizado.');
        }
        if (!$user->hasPermission($permission)) {
            abort(403, 'No tienes permiso: ' . $permission);
        }
        return $next($request);
    }
}
