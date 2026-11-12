@props(['variante' => 'primario', 'href' => null])

@php
    $clases = match ($variante) {
        'secundario' => 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-50 focus:ring-verde-500',
        'peligro' => 'bg-red-600 text-white hover:bg-red-500 focus:ring-red-500',
        'whatsapp' => 'bg-[#1f8f4e] text-white hover:bg-[#187a42] focus:ring-[#1f8f4e]',
        default => 'bg-verde-600 text-white hover:bg-verde-700 focus:ring-verde-500',
    };
    $clases = "inline-flex items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 $clases";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $clases]) }}>{{ $slot }}</a>
@else
    <button {{ $attributes->merge(['type' => 'submit', 'class' => $clases]) }}>{{ $slot }}</button>
@endif
