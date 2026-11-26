<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1">

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    <title>
        {{ $feriante->nombre }} | {{ config('app.name', 'PAIPPA Network') }}
    </title>

    <link
        rel="preconnect"
        href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet">

    @vite([
    'resources/css/app.css',
    'resources/js/app.js'
    ])
</head>


<body class="bg-[#F5F7F0] font-sans text-[#1E2A1F]">

    @php
    $palabras = preg_split('/\s+/', trim($feriante->nombre));

    $iniciales = collect($palabras)
    ->filter()
    ->take(2)
    ->map(fn ($palabra) => mb_strtoupper(mb_substr($palabra, 0, 1)))
    ->implode('');
    @endphp


    <div class="min-h-screen">

        {{-- Encabezado --}}
        <header class="border-b border-[#DDE4D3] bg-[#F5F7F0]">

            <div
                class="mx-auto flex max-w-7xl items-center
                       justify-between px-4 py-5
                       sm:px-6 lg:px-8">

                <a
                    href="{{ route('catalogo') }}"
                    class="flex items-center">
                    <img
                        src="{{ asset('images/paippa-v1-logo.svg') }}"
                        alt="PAIPPA Network"
                        class="h-10 w-auto">
                </a>


                <div class="flex items-center gap-3">

                    @guest

                    <a
                        href="{{ route('login') }}"
                        class="text-sm font-semibold text-paippa-green">
                        Ingresar
                    </a>

                    @if (Route::has('register'))

                    <a
                        href="{{ route('register') }}"
                        class="rounded-xl bg-paippa-green
                                       px-4 py-2 text-sm font-semibold
                                       text-white">
                        Registrarse
                    </a>

                    @endif

                    @else

                    @if (auth()->user()->esAdmin())

                    <a
                        href="{{ route('admin.inicio') }}"
                        class="text-sm font-semibold text-paippa-green">
                        Administración
                    </a>

                    @endif

                    <a
                        href="{{ route('profile.edit') }}"
                        class="text-sm font-semibold text-paippa-green">
                        Mi perfil
                    </a>

                    @endguest

                </div>

            </div>

        </header>


        <main
            class="mx-auto max-w-7xl px-4 py-6
                   sm:px-6 lg:px-8">

            {{-- Volver --}}
            <a
                href="{{ route('catalogo') }}"
                class="inline-flex items-center gap-1
                       text-sm font-semibold text-paippa-green
                       hover:underline">
                <span aria-hidden="true">‹</span>
                Volver al catálogo
            </a>


            {{-- Perfil del feriante --}}
            <section
                class="mt-5 rounded-2xl border border-[#DDE4D3]
                       bg-white p-5 shadow-sm">

                <div
                    class="flex flex-col gap-4
                           sm:flex-row sm:items-center">

                    {{-- Avatar --}}
                    <div
                        class="flex h-16 w-16 shrink-0
                               items-center justify-center
                               rounded-full bg-paippa-green
                               text-xl font-bold text-white">
                        {{ $iniciales ?: 'F' }}
                    </div>


                    {{-- Datos principales --}}
                    <div class="flex-1">

                        <h1 class="text-2xl font-bold text-[#1E2A1F]">
                            {{ $feriante->nombre }}
                        </h1>

                        <div
                            class="mt-1 flex flex-wrap items-center
                                   gap-x-2 gap-y-1 text-sm">

                            <span class="font-semibold text-paippa-green">
                                Validado
                            </span>

                            @if ($feriante->procedencia)

                            <span class="text-[#6B7A6C]">
                                ·
                            </span>

                            <span class="text-[#6B7A6C]">
                                Producción de {{ $feriante->procedencia }}
                            </span>

                            @endif

                        </div>


                        @if ($feriante->descripcion)

                        <p class="mt-3 max-w-2xl text-sm text-[#6B7A6C]">
                            {{ $feriante->descripcion }}
                        </p>

                        @endif

                    </div>

                </div>


                {{-- Datos de feria --}}
                <div
                    class="mt-6 grid grid-cols-1 gap-4
                           border-t border-[#DDE4D3]
                           pt-5 md:grid-cols-2">

                    <div>

                        <p
                            class="text-xs font-medium uppercase
                                   tracking-wide text-[#6B7A6C]">
                            Feria
                        </p>

                        <p class="mt-1 font-semibold text-[#1E2A1F]">
                            {{ $feriante->feria?->nombre ?? 'Sin feria' }}
                        </p>

                    </div>


                    <div>

                        <p
                            class="text-xs font-medium uppercase
                                   tracking-wide text-[#6B7A6C]">
                            Días y horario
                        </p>

                        <p class="mt-1 font-semibold text-[#1E2A1F]">
                            {{ $feriante->feria?->cuando ?: 'Sin información' }}
                        </p>

                    </div>

                </div>


                {{-- Puntos de retiro --}}
                <div class="mt-6">

                    <h2 class="text-sm font-bold text-[#1F4D22]">
                        Puntos de retiro
                    </h2>


                    @forelse ($feriante->feria?->puntosRetiro ?? [] as $punto)

                    <div
                        class="mt-3 rounded-xl border
                                   border-[#DDE4D3]
                                   bg-[#F5F7F0] p-4">

                        <p class="font-semibold text-[#1E2A1F]">
                            {{ $punto->nombre }}
                        </p>


                        <div class="mt-2 space-y-1 text-sm text-[#6B7A6C]">

                            <p>
                                <span class="font-semibold text-[#1E2A1F]">
                                    Dirección:
                                </span>

                                {{ $punto->direccion }}
                            </p>


                            <p>
                                <span class="font-semibold text-[#1E2A1F]">
                                    Horario:
                                </span>

                                {{ $punto->horario ?: 'Sin horario informado' }}
                            </p>

                        </div>

                    </div>

                    @empty

                    <p class="mt-2 text-sm text-[#6B7A6C]">
                        No hay puntos de retiro informados.
                    </p>

                    @endforelse

                </div>



                {{-- contacto por WhatsApp --}}
                @if ($ofertas->isNotEmpty())

                <div class="mt-6">

                    <x-boton-whatsapp
                        :oferta="$ofertas->first()"
                        class="w-full sm:w-auto" />

                </div>

                @endif

            </section>


            {{-- Ofertas vigentes --}}
            <section class="mt-8">

                <div class="mb-4">

                    <h2 class="text-xl font-bold text-[#1F4D22]">
                        Ofertas vigentes
                    </h2>

                    <p class="mt-1 text-sm text-[#6B7A6C]">
                        Productos disponibles actualmente de este feriante.
                    </p>

                </div>


                @if ($ofertas->isEmpty())

                <div
                    class="rounded-2xl border border-[#DDE4D3]
                               bg-white px-6 py-10 text-center">

                    <p class="font-semibold text-[#1E2A1F]">
                        No hay ofertas vigentes.
                    </p>

                    <p class="mt-1 text-sm text-[#6B7A6C]">
                        Este feriante no tiene productos publicados actualmente.
                    </p>

                </div>

                @else

                <div
                    class="grid grid-cols-1 gap-4
                               md:grid-cols-2
                               lg:grid-cols-3">

                    @foreach ($ofertas as $oferta)

                    <x-tarjeta-oferta :oferta="$oferta" />

                    @endforeach

                </div>

                @endif

            </section>

        </main>

    </div>

</body>

</html>