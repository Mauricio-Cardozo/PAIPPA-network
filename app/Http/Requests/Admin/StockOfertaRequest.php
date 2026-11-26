<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** PAI-58: actualización rápida de la cantidad disponible y la vigencia. */
class StockOfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('cantidad_disponible')) {
            $this->merge(['cantidad_disponible' => str_replace(',', '.', trim((string) $this->input('cantidad_disponible')))]);
        }
    }

    public function rules(): array
    {
        $desde = $this->route('oferta')->disponible_desde->toDateString();

        return [
            'cantidad_disponible' => ['required', 'numeric', 'min:0', 'max:99999999'],
            // RN-05: al actualizar, la oferta puede quedar vigente hasta 7 días desde hoy.
            'vigente_hasta' => ['required', 'date', 'after_or_equal:'.$desde, 'before_or_equal:'.today()->addDays(6)->toDateString()],
        ];
    }

    public function messages(): array
    {
        return [
            'vigente_hasta.after_or_equal' => 'La fecha «hasta» no puede ser anterior al inicio de la oferta.',
            'vigente_hasta.before_or_equal' => 'La oferta puede quedar vigente como máximo 7 días desde hoy.',
        ];
    }
}
