<?php

namespace App\Http\Controllers\Persona;

use App\Http\Controllers\Controller;
use Inertia\Inertia;  
use App\Models\User;
use App\Models\Persona;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    public function index()
    {
        {
        $users = User::with('persona:id,nombre')
            ->select('id','name','email','persona_id')
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

        // Si el cliente pide JSON (por fetch/axios), devuélvelo
        if (request()->wantsJson()) {
            return response()->json($users);
        }

        // Si es navegación normal, render Inertia
        return Inertia::render('Usuarios/Index', [
            'users' => $users,
        ]);
        }
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();

        // Si no te mandan persona_id, crea primero la persona tomando el nombre
        if (empty($data['persona_id'])) {
            $Persona = $data['nombre'] ?? $data['name'];
            $persona = Persona::create(['nombre' => $Persona]);
            $data['persona_id'] = $persona->id;
        }

        // Normaliza el nombre del usuario a 'name'
        $data['name'] = $data['name'] ?? ($data['nombre'] ?? null);
        unset($data['nombre']);

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        return response()->json($user->load('persona'), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load('persona'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if (isset($data['nombre']) && !isset($data['name'])) {
            $data['name'] = $data['nombre'];
            unset($data['nombre']);
        }

        $user->update($data);

        return response()->json($user->load('persona'));
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->noContent();
    }
}
