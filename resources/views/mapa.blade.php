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
        Mapa | {{ config('app.name', 'PAIPPA Network') }}
    </title>

    <link
        rel="preconnect"
        href="https://fonts.bunny.net">

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet">

    @vite([
    'resources/css/app.css',
    'resources/js/app.js',
    'resources/js/mapa.js'
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


                <a
                    href="{{ route('catalogo') }}"
                    class="text-sm font-semibold text-paippa-green">
                    Catálogo
                </a>

            </div>

        </header>


        <main
            class="mx-auto max-w-7xl
                   px-4 py-6 sm:px-6 lg:px-8">

            <div class="mb-5">

                <h1 class="text-2xl font-bold text-[#1F4D22]">
                    Mapa de puntos de retiro
                </h1>

                <p class="mt-1 text-sm text-[#6B7A6C]">
                    Consultá los puntos de retiro disponibles en Formosa Capital.
                </p>

            </div>


            <section
                class="overflow-hidden rounded-2xl
                       border border-[#DDE4D3]
                       bg-white shadow-sm">

                <div
                    id="mapa"
                    class="h-[65vh] min-h-[420px] w-full"></div>

            </section>

        </main>

    </div>

</body>

</html>