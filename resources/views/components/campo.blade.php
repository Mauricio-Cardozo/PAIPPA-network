{{--
    Campo de formulario completo: etiqueta, control y error.
    Uso: <x-campo nombre="nombre" etiqueta="Nombre" :valor="$categoria->nombre ?? ''" required />
    tipo="textarea" o tipo="select" (con las <option> en el slot).
--}}
@props(['nombre', 'etiqueta', 'tipo' => 'text', 'valor' => null, 'ayuda' => null])

@php
    $id = 'campo-'.str_replace(['[', ']', '.'], '-', $nombre);
    $actual = old($nombre, $valor);
    $clases = 'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-verde-500 focus:ring-verde-500 '
        .($errors->has($nombre) ? 'border-red-400' : '');
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'mb-4']) }}>
    <label for="{{ $id }}" class="block text-sm font-medium text-gray-700">{{ $etiqueta }}</label>

    @if ($tipo === 'textarea')
        <textarea id="{{ $id }}" name="{{ $nombre }}" rows="3" {{ $attributes->except('class')->merge(['class' => $clases]) }}>{{ $actual }}</textarea>
    @elseif ($tipo === 'select')
        <select id="{{ $id }}" name="{{ $nombre }}" {{ $attributes->except('class')->merge(['class' => $clases]) }}>
            {{ $slot }}
        </select>
    @else
        <input id="{{ $id }}" name="{{ $nombre }}" type="{{ $tipo }}" value="{{ $actual }}" {{ $attributes->except('class')->merge(['class' => $clases]) }}>
    @endif

    @if ($ayuda)
        <p class="mt-1 text-xs text-gray-500">{{ $ayuda }}</p>
    @endif

    @error($nombre)
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
