<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->withoutTrashed()],
            'nombre' => ['required', 'string', 'max:100'],
            'unidad' => ['required', 'string', 'max:30'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
