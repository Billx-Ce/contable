<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    /**
     * Actualiza la contraseña del usuario AUTENTICADO.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'], // verifica contra el usuario logueado
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Solo toca al usuario autenticado
        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return back()->with('status', 'password-updated'); // opcional: para mostrar mensaje en UI
    }
}
