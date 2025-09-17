<?php

namespace App\Http\Controllers\Persona;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Persona;
use App\Models\Role;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Requests\Auth\UpdateUserRolesRequest; // si lo usas
use Illuminate\Http\Request; // 👈 importante para updatePassword
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Lista paginada de usuarios con su persona.
     */
    public function index()
    {
        $users = User::with('persona:id,nombre')
            ->select('id', 'name', 'email', 'persona_id')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn ($u) => [
                'id'      => $u->id,
                'name'    => $u->name,
                'email'   => $u->email,
                'persona' => $u->persona
                    ? ['id' => $u->persona->id, 'nombre' => $u->persona->nombre]
                    : null,
            ]);

        if (request()->wantsJson()) {
            return response()->json($users);
        }

        return Inertia::render('Usuarios/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Crear usuario (JSON).
     */
    function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        if (empty($data['persona_id']) || !Persona::where('id', $data['persona_id'])->exists()) {
            return response()->json([
                'message' => 'El campo persona_id es obligatorio y debe existir en la tabla personas.'
            ], 422);
        }

        $data['name'] = $data['name'] ?? ($data['nombre'] ?? null);
        unset($data['nombre']);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        $clienteId = Role::where('nombre', 'cliente')->value('id');
        if ($clienteId) {
            $user->roles()->syncWithoutDetaching([$clienteId]);
        }

        return response()->json($user->load('persona'), 201);
    }

    /**
     * Formulario de edición de datos del usuario.
     */
    public function edit(User $user)
    {
        return Inertia::render('Usuarios/Edit', [
            'user' => $user->load('persona:id,nombre'),
        ]);
    }

    /**
     * Detalle JSON.
     */
    public function show(User $user)
    {
        return response()->json($user->load('persona'));
    }

    /**
     * Actualizar datos (email y/o password) — tu lógica actual.
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (!empty($data['current_password'])) {
            if (!Hash::check($data['current_password'], $user->password)) {
                return redirect()->back()->withErrors([
                    'current_password' => 'La contraseña actual es incorrecta.'
                ]);
            }
            $user->password = Hash::make($data['password']);
        }

        $user->email = $data['email'];
        $user->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado correctamente');
    }

    /**
     * 🔥 NUEVO: Actualizar solo la contraseña del usuario.
     */
    public function updatePassword(Request $request, User $user)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|confirmed|min:6',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            return redirect()->back()->withErrors([
                'current_password' => 'La contraseña actual es incorrecta.'
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('usuarios.index')
            ->with('success', 'Contraseña actualizada correctamente');
    }

    /**
     * 🔥 NUEVO: Formulario para asignar roles (si lo usas).
     */
    public function editRoles(User $user)
    {
        $roles = Role::orderBy('nombre')
            ->get(['id', 'nombre', 'descripcion', 'is_protected']);

        $currentRoleIds = $user->roles()->pluck('roles.id');

        return Inertia::render('Usuarios/Roles', [
            'user'           => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'roles'          => $roles,
            'currentRoleIds' => $currentRoleIds,
        ]);
    }

    /**
     * 🔥 NUEVO: Guardar roles asignados (si lo usas).
     */
    public function updateRoles(UpdateUserRolesRequest $request, User $user)
    {
        $roleIds = $request->input('roles', []);
        $user->roles()->sync($roleIds);

        return redirect()
            ->route('usuarios.index')
            ->with('success', 'Roles actualizados correctamente.');
    }

    /**
     * Eliminar usuario.
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }
}