<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FerianteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Deja solo los dígitos del teléfono antes de validar:
     * «+54 9 370 412-3456» pasa a «5493704123456».
     */
    protected function prepareForValidation(): void
    {
        // Una casilla sin marcar no se envía: se toma como «no autoriza».
        $this->merge(['autoriza_whatsapp' => $this->boolean('autoriza_whatsapp')]);

        if ($this->filled('telefono_whatsapp')) {
            $this->merge([
                'telefono_whatsapp' => preg_replace('/\D/', '', (string) $this->input('telefono_whatsapp')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'feria_id' => ['required', 'integer', Rule::exists('ferias', 'id')->withoutTrashed()],
            'nombre' => ['required', 'string', 'max:120'],
            'procedencia' => ['required', 'string', 'max:150'],
            'telefono_whatsapp' => ['required', 'regex:/^549\d{10}$/'],
            'autoriza_whatsapp' => ['boolean'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono_whatsapp.regex' => 'El teléfono de WhatsApp debe tener el formato 549 + código de área + número, sin el 0 ni el 15 (por ejemplo, 549 370 4123456).',
        ];
    }
}
