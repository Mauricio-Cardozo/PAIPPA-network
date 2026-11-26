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
        Catálogo | {{ config('app.name', 'PAIPPA Network') }}
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


                {{-- Menú --}}
                <div class="flex items-center gap-3">

                    @guest

                    <a
                        href="{{ route('login') }}"
                        class="text-sm font-semibold
                                   text-paippa-green">
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
                        class="text-sm font-semibold
                                       text-paippa-green">
                        Administración
                    </a>

                    @endif

                    <a
                        href="{{ route('profile.edit') }}"
                        class="text-sm font-semibold
                                   text-paippa-green">
                        Mi perfil
                    </a>

                    @endguest

                </div>

            </div>

        </header>


        <main
            class="mx-auto max-w-7xl
                   px-4 py-6 sm:px-6 lg:px-8">

            {{-- Título --}}
            <div class="mb-5">

                <h1 class="text-2xl font-bold text-[#1F4D22]">
                    Catálogo
                </h1>

                <p class="mt-1 text-sm text-[#6B7A6C]">
                    Ofertas disponibles de feriantes del PAIPPA.
                </p>

            </div>


            @php
      
            $categoriasFiltro = $categorias
            ?? $ofertas->getCollection()
            ->pluck('producto.categoria')
            ->filter()
            ->unique('id')
            ->sortBy('nombre');

            $feriasFiltro = $ferias
            ?? $ofertas->getCollection()
            ->pluck('feriante.feria')
            ->filter()
            ->unique('id')
            ->sortBy('nombre');
            @endphp


            <x-filtros-catalogo
                :categorias="$categoriasFiltro"
                :ferias="$feriasFiltro" />

            {{-- Tarjetas --}}
            @forelse ($ofertas as $oferta)

            @if ($loop->first)

            <section
                class="grid grid-cols-1 gap-4
                               md:grid-cols-2
                               lg:grid-cols-3
                               xl:grid-cols-4">

                @endif


                <x-tarjeta-oferta
                    :oferta="$oferta" />


                @if ($loop->last)

            </section>

            @endif


            @empty

            {{-- catálogo sin resultados --}}
            <section
                class="rounded-2xl border border-[#DDE4D3]
                           bg-white px-6 py-12 text-center">

                <svg
                    class="mx-auto h-10 w-10 text-paippa-green"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24">
                    <circle
                        cx="11"
                        cy="11"
                        r="7"
                        stroke-width="2" />

                    <path
                        d="m20 20-3.5-3.5"
                        stroke-width="2"
                        stroke-linecap="round" />
                </svg>


                <h2
                    class="mt-4 text-lg font-bold
                               text-[#1E2A1F]">
                    No encontramos ofertas
                </h2>

                <p
                    class="mx-auto mt-2 max-w-sm
                               text-sm text-[#6B7A6C]">
                    Probá nuevamente más tarde o modificá
                    los filtros cuando estén disponibles.
                </p>

            </section>

            @endforelse


            {{-- Paginación --}}
            @if ($ofertas->hasPages())

            <div class="mt-6">
                {{ $ofertas->links() }}
            </div>

            @endif

        </main>

    </div>

</body>

</html>