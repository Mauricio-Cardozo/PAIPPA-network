<?php

namespace App\Http\Requests\Admin;

use App\Enums\TipoFeria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FeriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Acepta coma decimal en las coordenadas («-26,1857») y la convierte a punto. */
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
            'nombre' => ['required', 'string', 'max:100', Rule::unique('ferias', 'nombre')->withoutTrashed()->ignore($this->route('feria'))],
            'tipo' => ['required', Rule::enum(TipoFeria::class)],
            'direccion' => ['required', 'string', 'max:150'],
            'barrio' => ['required', 'string', 'max:100'],
            // Las fijas tienen días de la semana; las itinerantes, una fecha.
            'dias' => ['nullable', 'required_if:tipo,fija', 'string', 'max:50'],
            'fecha' => ['nullable', 'required_if:tipo,itinerante', 'date'],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
            'latitud' => ['nullable', 'numeric', 'between:-90,90', 'required_with:longitud'],
            'longitud' => ['nullable', 'numeric', 'between:-180,180', 'required_with:latitud'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /** Lo que no corresponde al tipo elegido se guarda vacío. */
    public function validated($key = null, $default = null): mixed
    {
        $datos = parent::validated($key, $default);

        if ($key === null) {
            if ($datos['tipo'] === TipoFeria::Fija->value) {
                $datos['fecha'] = null;
            } else {
                $datos['dias'] = null;
            }
        }

        return $datos;
    }
}
