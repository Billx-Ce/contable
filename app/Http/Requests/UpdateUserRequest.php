<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtén el ID del modelo enlazado por ruta {user} (o {usuario}/{usuarios} si lo nombraste así)
        $currentId = $this->route('user')?->id
                   ?? $this->route('usuario')?->id
                   ?? $this->route('usuarios')?->id;

        return [
            'name'       => ['sometimes','string','max:100'],
            'nombre'     => ['sometimes','string','max:100'],

            'email'      => ['sometimes','email','max:150', Rule::unique('users','email')->ignore($currentId)],
            'password'   => ['sometimes','nullable','string','min:6'],

            'persona_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:personas,id',
                Rule::unique('users','persona_id')->ignore($currentId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'persona_id.unique' => 'Esa persona ya está vinculada a otro usuario.',
        ];
    }
}
