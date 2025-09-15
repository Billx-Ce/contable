<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre'      => ['required','string','max:100','unique:permissions,nombre'],
            'descripcion' => ['nullable','string'],
            'grupo'       => ['nullable','string','max:100'],
        ];
    }
}
