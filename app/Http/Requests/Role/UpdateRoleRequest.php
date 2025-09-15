<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $roleId = $this->route('role')->id ?? null;

        return [
            'nombre'        => ['sometimes','string','max:100', Rule::unique('roles','nombre')->ignore($roleId)],
            'descripcion'   => ['sometimes','nullable','string'],
            'is_protected'  => ['sometimes','boolean'],
            'permissions'   => ['sometimes','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ];
    }
}
