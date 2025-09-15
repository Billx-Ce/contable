<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('permission')->id ?? null;

        return [
            'nombre'      => ['sometimes','string','max:100', Rule::unique('permissions','nombre')->ignore($id)],
            'descripcion' => ['sometimes','nullable','string'],
            'grupo'       => ['sometimes','nullable','string','max:100'],
        ];
    }
}
