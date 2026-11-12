<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validación del registro público de revendedores.
 */
class RegistroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** El teléfono se guarda solo con dígitos. */
    protected function prepareForValidation(): void
    {
        if ($this->filled('telefono')) {
            $this->merge(['telefono' => preg_replace('/\D/', '', (string) $this->input('telefono'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'telefono' => ['nullable', 'string', 'min:8', 'max:15'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
