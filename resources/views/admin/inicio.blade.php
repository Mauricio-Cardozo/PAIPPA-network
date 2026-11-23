
<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-paippa-green">
            Panel de administración
        </h2>
    </x-slot>

    <div class="py-8 bg-[#F8F9F5] min-h-screen">

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Indicadores -->
            <div class="grid grid-cols-2 gap-4">

                <!-- Feriantes -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-sm text-gray-500">
                        Feriantes
                    </p>

                    <p class="text-3xl font-bold text-paippa-green mt-2">
                        {{ $totalFeriantes }}
                    </p>
                </div>

                <!-- Ofertas -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-sm text-gray-500">
                        Ofertas vigentes
                    </p>

                    <p class="text-3xl font-bold text-paippa-green mt-2">
                        {{ $totalOfertasVigentes }}
                    </p>
                </div>

                <!-- Ferias -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-sm text-gray-500">
                        Ferias
                    </p>

                    <p class="text-3xl font-bold text-paippa-green mt-2">
                        {{ $totalFerias }}
                    </p>
                </div>

                <!-- Usuarios -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <p class="text-sm text-gray-500">
                        Usuarios
                    </p>

                    <p class="text-3xl font-bold text-paippa-green mt-2">
                        {{ $totalUsuarios }}
                    </p>
                </div>

            </div>

            <!-- Gestión -->
            <section class="mt-10">

                <h2 class="text-xl font-bold text-paippa-green mb-5">
                    Gestión
                </h2>

                <div class="space-y-3">

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800">
                                Feriantes
                            </span>
                            <span class="text-xs text-gray-500">
                                Próximamente
                            </span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800">
                                Ferias y puntos de retiro
                            </span>
                            <span class="text-xs text-gray-500">
                                Próximamente
                            </span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800">
                                Productos y categorías
                            </span>
                            <span class="text-xs text-gray-500">
                                Próximamente
                            </span>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <div class="flex justify-between items-center">
                            <span class="font-medium text-gray-800">
                                Ofertas
                            </span>
                            <span class="text-xs text-gray-500">
                                Próximamente
                            </span>
                        </div>
                    </div>

                </div>

            </section>

        </div>
    </div>

</x-app-layout>
