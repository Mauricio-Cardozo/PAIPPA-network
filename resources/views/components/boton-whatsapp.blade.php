@props(['oferta'])

@auth

<a
    href="{{ route('contacto.whatsapp', $oferta) }}"
    target="_blank"
    rel="noopener"
    {{ $attributes->merge([
            'class' => 'block rounded-xl bg-paippa-green px-4 py-3 text-center text-sm font-semibold text-white transition hover:opacity-90'
        ]) }}>
    Contactar por WhatsApp
</a>

@else

<a
    href="{{ route('contacto.login', $oferta) }}"
    {{ $attributes->merge([
            'class' => 'block rounded-xl border border-paippa-green bg-white px-4 py-3 text-center text-sm font-semibold text-paippa-green transition hover:bg-green-50'
        ]) }}>
    Iniciá sesión para contactar
</a>

@endauth