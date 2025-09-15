<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Permission;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        $isSuperadmin = false;
        $can = [];

        if ($user) {
            $isSuperadmin = $user->roles()->where('nombre', 'superadmin')->exists();

            if ($isSuperadmin) {
                $can = Permission::pluck('nombre')->toArray();
            } else {
                $roleIds = $user->roles()->pluck('roles.id');

                $can = Permission::whereHas('roles', function ($q) use ($roleIds) {
                        $q->whereIn('roles.id', $roleIds);
                    })
                    ->pluck('nombre')
                    ->unique()
                    ->values()
                    ->toArray();
            }
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => fn () => $user ? [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'is_admin' => (bool) ($user->is_admin ?? false),
                ] : null,
                'isSuperadmin' => $isSuperadmin,
                'can'          => $can,
            ],
        ]);
    }
}
