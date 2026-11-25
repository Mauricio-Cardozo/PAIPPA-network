<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PuntoRetiroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Acepta coma decimal («-26,1857») y la convierte a punto. */
    protected function prepareForValidation(): void
    {
        foreach (['latitud', 'longitud'] as $campo) {
            if ($this->filled($campo)) {
                $this->merge([$campo => str_replace(',', '.', trim((string) $this->input($campo)))]);
            }
        }
    }

    public function rules(): array
    {
        return [
            'feria_id' => ['required', 'integer', Rule::exists('ferias', 'id')->withoutTrashed()],
            'nombre' => ['required', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'max:200'],
            'horario' => ['required', 'string', 'max:150'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
        ];
    }
}
