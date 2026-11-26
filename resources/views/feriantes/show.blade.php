{{--
    PAI-68: vista mínima del perfil para que la ruta funcione.
    El diseño final de esta pantalla lo hace Apolo en la PAI-67 (P05 de Figma).
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $feriante->nombre }} | {{ config('app.name', 'PAIPPA network') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans text-gray-900 antialiased">
    <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        <a href="{{ route('catalogo') }}" class="text-sm font-medium text-paippa-green hover:underline">‹ Volver al catálogo</a>

        <section class="mt-4 rounded-2xl bg-white p-5 shadow-sm">
            <h1 class="text-2xl font-bold text-paippa-green">{{ $feriante->nombre }}</h1>
            <p class="text-sm text-gray-600">Validado por el PAIPPA · Producción de {{ $feriante->procedencia ?? '—' }}</p>
            @if ($feriante->descripcion)
                <p class="mt-3 text-sm">{{ $feriante->descripcion }}</p>
            @endif

            <dl class="mt-4 grid gap-2 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500">Feria</dt><dd class="font-medium">{{ $feriante->feria?->nombre }} · {{ $feriante->feria?->cuando }}</dd></div>
                <div>
                    <dt class="text-gray-500">Puntos de retiro</dt>
                    @foreach ($feriante->feria?->puntosRetiro ?? [] as $punto)
                        <dd class="font-medium">{{ $punto->nombre }} · {{ $punto->horario }}</dd>
                    @endforeach
                </div>
            </dl>
        </section>

        <h2 class="mt-6 text-lg font-semibold">Ofertas vigentes</h2>
        @if ($ofertas->isEmpty())
            <p class="mt-2 rounded-2xl bg-white p-6 text-center text-gray-500 shadow-sm">Este feriante no tiene ofertas vigentes por ahora.</p>
        @else
            <div class="mt-3 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($ofertas as $oferta)
                    <x-tarjeta-oferta :oferta="$oferta" />
                @endforeach
            </div>
        @endif
    </main>
</body>
</html>
