{{--
    Botón para contactar al feriante por WhatsApp.
    - Con sesión: va a la ruta que registra el clic y abre WhatsApp.
    - Sin sesión: lleva al login y, después de ingresar, vuelve a esta página.
    Uso: <x-boton-whatsapp :oferta="$oferta" />  o  <x-boton-whatsapp :feriante="$feriante" />
--}}
@props(['oferta' => null, 'feriante' => null])

@auth
    <a href="{{ $oferta ? route('contacto.whatsapp', $oferta) : route('contacto.whatsapp.feriante', $feriante) }}"
       target="_blank" rel="noopener"
       {{ $attributes->merge(['class' => 'inline-flex w-full items-center justify-center gap-2 rounded-md bg-[#1f8f4e] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#187a42] focus:outline-none focus:ring-2 focus:ring-[#1f8f4e] focus:ring-offset-2']) }}>
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2Zm0 18.2c-1.5 0-3-.4-4.3-1.2l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2Zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.3-.4.2-.4.7-1.4.1-.2 0-.3 0-.5l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.8 11.8 11.8 0 0 0 4.6 4c1.7.7 2.3.8 3.2.7.5-.1 1.5-.6 1.8-1.2.2-.6.2-1.1.1-1.2l-.4-.3Z"/></svg>
        Contactar por WhatsApp
    </a>
@else
    <a href="{{ route('login', ['volver' => url()->current()]) }}"
       {{ $attributes->merge(['class' => 'inline-flex w-full items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50']) }}>
        Iniciá sesión para contactar
    </a>
@endauth
