<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validación para crear y editar categorías (la usa store y update).
 */
class CategoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El acceso ya lo controla el middleware rol:admin de las rutas.
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => [
                'required', 'string', 'max:100',
                // Único entre las categorías activas; al editar, se ignora a sí misma.
                Rule::unique('categorias', 'nombre')
                    ->withoutTrashed()
                    ->ignore($this->route('categoria')),
            ],
        ];
    }
}
