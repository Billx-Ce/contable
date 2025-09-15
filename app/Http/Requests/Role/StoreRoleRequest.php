<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'        => ['required','string','max:100','unique:roles,nombre'],
            'descripcion'   => ['nullable','string'],
            'is_protected'  => ['boolean'],
            'permissions'   => ['sometimes','array'],
            'permissions.*' => ['integer','exists:permissions,id'],
        ];
    }
}
