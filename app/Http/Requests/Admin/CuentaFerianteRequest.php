<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class CuentaFerianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // La tabla feriantes no tiene correo: se pide acá y va a la cuenta (users).
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
        ];
    }
}
