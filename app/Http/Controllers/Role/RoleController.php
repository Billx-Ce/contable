<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(Request $request): Response
    {
        $roles = Role::withCount('users', 'permissions')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Roles/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100','unique:roles,nombre'],
            'descripcion' => ['nullable','string'],
        ]);

        Role::create($data);
        return back()->with('success', 'Rol creado.');
    }

    public function edit(Role $role): \Inertia\Response
    {
    // Permisos marcados para este rol
    $selectedIds = $role->permissions()->pluck('permissions.id')->toArray();

    // Todos los permisos agrupados por 'grupo' (para mostrar en la vista)
    $permissions = Permission::orderBy('grupo')->orderBy('nombre')
        ->get()
        ->groupBy('grupo')
        ->map(function ($items, $group) {
            return [
                'grupo' => $group ?: 'Sin grupo',
                'items' => $items->map(fn ($p) => [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'descripcion' => $p->descripcion,
                ])->values(),
            ];
        })->values();

    return \Inertia\Inertia::render('Roles/Edit', [
        'role'          => $role->only(['id','nombre','descripcion','is_protected']),
        'selectedIds'   => $selectedIds,
        'permissions'   => $permissions,
    ]);
    }

    public function update(\Illuminate\Http\Request $request, Role $role)
    {
    $data = $request->validate([
        'nombre' => ['required','string','max:100',"unique:roles,nombre,{$role->id}"],
        'descripcion' => ['nullable','string'],
        'permission_ids' => ['nullable','array'],
        'permission_ids.*' => ['integer','exists:permissions,id'],
    ]);

    $role->update([
        'nombre' => $data['nombre'],
        'descripcion' => $data['descripcion'] ?? null,
    ]);

    // si viene el array, sincroniza permisos
    if (array_key_exists('permission_ids', $data)) {
        $role->permissions()->sync($data['permission_ids']);
    }

    return back()->with('success', 'Rol actualizado.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_protected) {
            return back()->with('error', 'No puedes eliminar un rol protegido.');
        }
        $role->delete();
        return back()->with('success', 'Rol eliminado.');
    }
}
