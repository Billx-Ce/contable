<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        // Permite si es superadmin (acepta 'superadmin' o 'superadministrador')
        if ($user && $user->roles()->whereIn('nombre', ['superadmin','superadministrador'])->exists()) {
            return true;
        }

        // O si tiene el permiso específico para administrar roles
        return $user?->hasPermission('roles.administrar') ?? false;
    }

    public function rules(): array
    {
        return [
            'roles'   => ['array'],
            'roles.*' => ['integer', 'exists:roles,id'],
        ];
    }
}
