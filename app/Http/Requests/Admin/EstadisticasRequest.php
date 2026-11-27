<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** PAI-90: rango de fechas del panel de estadísticas (?desde=&hasta=). */
class EstadisticasRequest extends FormRequest
{
    /** Si una fecha no es válida, vuelve al panel sin filtros y muestra el error. */
    protected $redirectRoute = 'admin.estadisticas';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'desde' => ['nullable', 'date_format:Y-m-d'],
            'hasta' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ];
    }

    public function messages(): array
    {
        return [
            'date_format' => 'La fecha :attribute no es válida.',
            'hasta.after_or_equal' => 'La fecha «hasta» no puede ser anterior a «desde».',
        ];
    }

    public function attributes(): array
    {
        return ['desde' => '«desde»', 'hasta' => '«hasta»'];
    }
}
