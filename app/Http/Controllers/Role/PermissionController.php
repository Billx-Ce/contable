<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PermissionController extends Controller
{
    public function index(Request $request): Response
    {
        $permissions = Permission::orderBy('grupo')->orderBy('nombre')
            ->paginate(20)->withQueryString();

        return Inertia::render('Permisos/Index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Permisos/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100','unique:permissions,nombre'],
            'descripcion' => ['nullable','string'],
            'grupo' => ['nullable','string','max:100'],
        ]);
        Permission::create($data);
        return back()->with('success', 'Permiso creado.');
    }

    public function edit(Permission $permission): Response
    {
        return Inertia::render('Permisos/Edit', [
            'permission' => $permission,
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'nombre' => ['required','string','max:100',"unique:permissions,nombre,{$permission->id}"],
            'descripcion' => ['nullable','string'],
            'grupo' => ['nullable','string','max:100'],
        ]);
        $permission->update($data);
        return back()->with('success', 'Permiso actualizado.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return back()->with('success', 'Permiso eliminado.');
    }
}
