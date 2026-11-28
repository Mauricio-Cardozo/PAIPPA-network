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

    /**
     * Completa las fechas que faltan antes de validar, así la regla compara
     * siempre: «hasta» es hoy si no viene, y «desde» son los 30 días anteriores
     * a «hasta». Un «desde» futuro sin «hasta» queda después de hoy y da error.
     */
    protected function prepareForValidation(): void
    {
        $hasta = $this->filled('hasta') ? $this->input('hasta') : today()->toDateString();
        $desde = $this->filled('desde') ? $this->input('desde') : $this->desdePorDefecto($hasta);

        $this->merge(['desde' => $desde, 'hasta' => $hasta]);
    }

    /** 30 días contando «hasta». Si «hasta» no es una fecha válida, la regla lo informa. */
    private function desdePorDefecto(mixed $hasta): ?string
    {
        $fecha = is_string($hasta) ? \DateTime::createFromFormat('!Y-m-d', $hasta) : false;

        return $fecha ? $fecha->modify('-29 days')->format('Y-m-d') : null;
    }

    public function rules(): array
    {
        return [
            'desde' => ['required', 'date_format:Y-m-d'],
            'hasta' => ['required', 'date_format:Y-m-d', 'after_or_equal:desde'],
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
