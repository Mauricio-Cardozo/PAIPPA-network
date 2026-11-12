<?php

namespace App\Http\Requests\Admin;

use App\Models\Feriante;
use App\Models\PuntoRetiro;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfertaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Solo se cargan ofertas de feriantes validados.
            'feriante_id' => ['required', 'integer', Rule::exists('feriantes', 'id')->withoutTrashed()->whereNotNull('validado_at')],
            'producto_id' => ['required', 'integer', Rule::exists('productos', 'id')->withoutTrashed()],
            'punto_retiro_id' => [
                'required', 'integer', Rule::exists('puntos_retiro', 'id')->withoutTrashed(),
                // El punto de retiro tiene que ser de la misma feria que el feriante.
                function (string $atributo, mixed $valor, Closure $fallar) {
                    $feriante = Feriante::find($this->input('feriante_id'));
                    $punto = PuntoRetiro::find($valor);

                    if ($feriante && $punto && $punto->feria_id !== $feriante->feria_id) {
                        $fallar('El punto de retiro tiene que ser de la feria del feriante.');
                    }
                },
            ],
            'precio_caja' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'cajas_disponibles' => ['required', 'integer', 'min:0', 'max:100000'],
            'vigente_desde' => ['required', 'date'],
            'vigente_hasta' => ['required', 'date', 'after_or_equal:vigente_desde'],
        ];
    }

    public function messages(): array
    {
        return [
            'feriante_id.exists' => 'El feriante tiene que estar validado para cargarle ofertas.',
        ];
    }
}
