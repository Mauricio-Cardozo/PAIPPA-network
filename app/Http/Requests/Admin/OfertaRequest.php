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

    /** Acepta coma decimal («2500,50») y las casillas sin marcar cuentan como «no». */
    protected function prepareForValidation(): void
    {
        foreach (['precio', 'cantidad_disponible'] as $campo) {
            if ($this->filled($campo)) {
                $this->merge([$campo => str_replace(',', '.', trim((string) $this->input($campo)))]);
            }
        }

        $this->merge([
            'promocion' => $this->boolean('promocion'),
            'entrega_domicilio' => $this->boolean('entrega_domicilio'),
        ]);
    }

    public function rules(): array
    {
        return [
            // Solo se cargan ofertas de feriantes validados (RN-01, RN-02).
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
            'precio' => ['required', 'numeric', 'gt:0', 'max:99999999'],
            'cantidad_disponible' => ['required', 'numeric', 'min:0', 'max:99999999'],
            'disponible_desde' => ['required', 'date'],
            // RN-05: la oferta vale como máximo 7 días; después hay que actualizarla.
            'vigente_hasta' => ['required', 'date', 'after_or_equal:disponible_desde', $this->hastaSieteDias()],
            'promocion' => ['boolean'],
            'entrega_domicilio' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'feriante_id.exists' => 'El feriante tiene que estar validado para cargarle ofertas.',
        ];
    }

    /** Regla «como máximo 7 días después de disponible_desde» (RN-05). */
    private function hastaSieteDias(): Closure
    {
        return function (string $atributo, mixed $valor, Closure $fallar) {
            $desde = strtotime((string) $this->input('disponible_desde'));
            $hasta = strtotime((string) $valor);

            if ($desde && $hasta && $hasta > strtotime('+6 days', $desde)) {
                $fallar('La oferta puede estar vigente como máximo 7 días: después hay que actualizarla.');
            }
        };
    }
}
