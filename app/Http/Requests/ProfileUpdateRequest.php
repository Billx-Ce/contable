<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check(); // agregado: solo usuarios logueados
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // agregado: email único ignorando al propio usuario
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore(auth()->id()),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'El formato del correo no es válido.',
            'email.unique'   => 'Este correo ya está en uso.',
        ];
    }
}
