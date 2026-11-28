
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

                {{-- Accesos a cada ABM del panel (PAI-140) --}}
                <div class="space-y-3">

                    <a href="{{ route('admin.feriantes.index') }}"
                       class="block bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:border-paippa-green">
                        <div class="flex justify-between items-center gap-4">
                            <span class="font-medium text-gray-800">
                                Feriantes
                            </span>
                            <span class="text-xs {{ $feriantesPendientes > 0 ? 'font-semibold text-amber-700' : 'text-gray-500' }}">
                                {{ $feriantesPendientes }} {{ $feriantesPendientes === 1 ? 'pendiente' : 'pendientes' }} de validar
                            </span>
                        </div>
                    </a>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <span class="font-medium text-gray-800">
                            Ferias y puntos de retiro
                        </span>
                        <div class="mt-2 flex gap-4 text-sm">
                            <a href="{{ route('admin.ferias.index') }}" class="font-semibold text-paippa-green hover:underline">Ferias</a>
                            <a href="{{ route('admin.puntos-retiro.index') }}" class="font-semibold text-paippa-green hover:underline">Puntos de retiro</a>
                        </div>
                    </div>

                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                        <span class="font-medium text-gray-800">
                            Productos y categorías
                        </span>
                        <div class="mt-2 flex gap-4 text-sm">
                            <a href="{{ route('admin.productos.index') }}" class="font-semibold text-paippa-green hover:underline">Productos</a>
                            <a href="{{ route('admin.categorias.index') }}" class="font-semibold text-paippa-green hover:underline">Categorías</a>
                        </div>
                    </div>

                    <a href="{{ route('admin.ofertas.index') }}"
                       class="block bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:border-paippa-green">
                        <div class="flex justify-between items-center gap-4">
                            <span class="font-medium text-gray-800">
                                Ofertas
                            </span>
                            <span class="text-xs text-gray-500">
                                Cargar y actualizar
                            </span>
                        </div>
                    </a>

                    <a href="{{ route('admin.usuarios.index') }}"
                       class="block bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:border-paippa-green">
                        <div class="flex justify-between items-center gap-4">
                            <span class="font-medium text-gray-800">
                                Usuarios
                            </span>
                            <span class="text-xs text-gray-500">
                                Ver y bloquear
                            </span>
                        </div>
                    </a>

                    <a href="{{ route('admin.estadisticas') }}"
                       class="block bg-white rounded-xl border border-gray-200 shadow-sm p-5 hover:border-paippa-green">
                        <div class="flex justify-between items-center gap-4">
                            <span class="font-medium text-gray-800">
                                Estadísticas
                            </span>
                            <span class="text-xs text-gray-500">
                                Consultas y contactos
                            </span>
                        </div>
                    </a>

                </div>

            </section>

        </div>
    </div>

</x-app-layout>
