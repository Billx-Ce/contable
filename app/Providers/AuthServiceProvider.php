<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // Puedes mapear modelos a policies aquí si las usas
    ];

    public function boot(): void
    {
        // Habilita que cualquier `can:algo` consulte tu método User::hasPermission('algo')
        Gate::before(function ($user, string $ability) {
            return method_exists($user, 'hasPermission') && $user->hasPermission($ability)
                ? true
                : null; // null => sigue el flujo normal
        });
    }
}
