<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * PAI-64: parámetros de los filtros del catálogo (?q=, ?categoria=, ?feria=, ?precio_max=).
 *
 * El catálogo es público y los parámetros vienen en la URL, así que un valor
 * raro (?categoria=abc) no da error: se descarta y el catálogo se muestra sin
 * ese filtro. Nunca se arma SQL con estos valores (se usan con bindings).
 */
class CatalogoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $q = trim((string) $this->query('q', ''));
        $precio = str_replace(',', '.', trim((string) $this->query('precio_max', '')));

        $this->merge([
            'q' => $q === '' ? null : mb_substr($q, 0, 100),
            'categoria' => $this->enteroPositivo('categoria'),
            'feria' => $this->enteroPositivo('feria'),
            'precio_max' => is_numeric($precio) && (float) $precio > 0 ? (float) $precio : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'categoria' => ['nullable', 'integer', 'min:1'],
            'feria' => ['nullable', 'integer', 'min:1'],
            'precio_max' => ['nullable', 'numeric', 'gt:0'],
        ];
    }

    /** Devuelve el número si es un entero positivo; si no, null (se ignora el filtro). */
    private function enteroPositivo(string $campo): ?int
    {
        $valor = $this->query($campo);

        return is_string($valor) && ctype_digit($valor) && (int) $valor > 0 ? (int) $valor : null;
    }
}
