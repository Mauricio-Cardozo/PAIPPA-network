<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

/**
 * Filtros del catálogo (vienen en la URL, formulario GET).
 * Un parámetro raro (?categoria=abc, ?precio_max=hola) no da error ni 500:
 * se descarta y el catálogo se muestra sin ese filtro.
 */
class CatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $limpio = [];

        $q = trim($this->texto('q'));
        $limpio['q'] = $q === '' ? null : Str::limit($q, 100, '');

        foreach (['categoria', 'feria'] as $campo) {
            $valor = $this->texto($campo);
            $limpio[$campo] = ctype_digit($valor) ? (int) $valor : null;
        }

        $precio = str_replace(',', '.', $this->texto('precio_max'));
        $limpio['precio_max'] = is_numeric($precio) && $precio >= 0 ? (float) $precio : null;

        $this->merge($limpio);
    }

    /** Lee un parámetro como texto; si vino como lista (?q[]=...), lo ignora. */
    private function texto(string $campo): string
    {
        $valor = $this->query($campo, '');

        return is_string($valor) ? $valor : '';
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer'],
            'feria' => ['nullable', 'integer'],
            'precio_max' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    /** Los filtros ya limpios, listos para Oferta::filtrar(). */
    public function filtros(): array
    {
        return array_filter($this->validated(), fn ($valor) => $valor !== null);
    }
}
