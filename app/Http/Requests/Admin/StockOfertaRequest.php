<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Actualización rápida de cajas y vigencia desde el listado de ofertas. */
class StockOfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $oferta = $this->route('oferta');

        return [
            'cajas_disponibles' => ['required', 'integer', 'min:0', 'max:100000'],
            'vigente_hasta' => ['required', 'date', 'after_or_equal:'.$oferta->vigente_desde->toDateString()],
        ];
    }

    public function messages(): array
    {
        return [
            'vigente_hasta.after_or_equal' => 'La fecha «hasta» no puede ser anterior al inicio de la oferta.',
        ];
    }
}
