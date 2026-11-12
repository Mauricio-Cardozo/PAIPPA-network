{{-- Muestra los mensajes flash de la sesión: 'exito', 'error' y 'status' (Breeze). --}}
@foreach (['exito' => 'bg-verde-50 border-verde-300 text-verde-800', 'status' => 'bg-verde-50 border-verde-300 text-verde-800', 'error' => 'bg-red-50 border-red-300 text-red-800'] as $clave => $clases)
    @if (session($clave) && ! in_array(session($clave), ['profile-updated', 'password-updated', 'verification-link-sent'], true))
        <div role="status" {{ $attributes->merge(['class' => "mb-4 rounded-md border px-4 py-3 text-sm $clases"]) }}>
            {{ session($clave) }}
        </div>
    @endif
@endforeach
