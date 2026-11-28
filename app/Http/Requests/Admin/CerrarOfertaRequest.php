<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** PAI-150 (RN-17): al cerrar una oferta se puede anotar cuánto se vendió. */
class CerrarOfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** Acepta la coma decimal, como el resto del panel (2,5 kilos). */
    protected function prepareForValidation(): void
    {
        if ($this->filled('cantidad_vendida')) {
            $this->merge(['cantidad_vendida' => str_replace(',', '.', trim((string) $this->input('cantidad_vendida')))]);
        }
    }

    public function rules(): array
    {
        return [
            // Opcional: si no se sabe cuánto se vendió, se cierra igual.
            'cantidad_vendida' => ['nullable', 'numeric', 'min:0', 'max:'.$this->cantidadOfrecida()],
        ];
    }

    /**
     * La mayor cantidad que tuvo la oferta, según el historial (RN-15). No se usa
     * la que queda: una oferta agotada tiene 0 y vendió todo. El max() con la
     * actual cubre las ofertas sin historial, como las del seeder.
     */
    private function cantidadOfrecida(): float
    {
        $oferta = $this->route('oferta');

        return max((float) $oferta->cantidad_disponible, (float) $oferta->historial()->max('cantidad_disponible'));
    }

    public function messages(): array
    {
        return [
            'cantidad_vendida.numeric' => 'La cantidad vendida tiene que ser un número.',
            'cantidad_vendida.min' => 'La cantidad vendida no puede ser negativa.',
            'cantidad_vendida.max' => 'La cantidad vendida no puede ser mayor que la cantidad ofrecida.',
        ];
    }
}
