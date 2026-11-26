@props([
'categorias' => collect(),
'ferias' => collect(),
])

<form
    method="GET"
    action="{{ route('catalogo') }}"
    class="mb-6"
    x-data="{ abierto: false }">

    {{-- Buscador --}}
    <div class="flex gap-2">

        <div class="relative flex-1">

            <svg
                class="pointer-events-none absolute left-4 top-1/2
                       h-5 w-5 -translate-y-1/2 text-[#6B7A6C]"
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

            <input
                type="search"
                name="q"
                value="{{ request('q') }}"
                placeholder="Buscar producto..."
                class="w-full rounded-xl border-[#DDE4D3]
                       bg-white py-3 pl-12 pr-4
                       text-sm text-[#1E2A1F]
                       placeholder:text-[#6B7A6C]
                       focus:border-paippa-green
                       focus:ring-paippa-green">

        </div>


        {{-- Botón filtros solamente en celular --}}
        <button
            type="button"
            @click="abierto = !abierto"
            class="flex items-center gap-2 rounded-xl
                   border border-[#DDE4D3] bg-white
                   px-4 py-3 text-sm font-semibold
                   text-[#1F4D22] md:hidden">
            Filtros

            <svg
                class="h-4 w-4 transition"
                :class="{ 'rotate-180': abierto }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="m6 9 6 6 6-6" />
            </svg>
        </button>

    </div>


    {{-- Filtros --}}
    <div
        class="mt-3 gap-3 md:grid md:grid-cols-4"
        :class="abierto ? 'grid grid-cols-1 sm:grid-cols-2' : 'hidden'">

        {{-- Categoría --}}
        <div>

            <label
                for="categoria"
                class="mb-1 block text-xs font-medium text-[#6B7A6C]">
                Categoría
            </label>

            <select
                id="categoria"
                name="categoria"
                class="w-full rounded-xl border-[#DDE4D3]
                       bg-white px-3 py-2.5
                       text-sm text-[#1F4D22]
                       focus:border-paippa-green
                       focus:ring-paippa-green">

                <option value="">
                    Todas
                </option>

                @foreach ($categorias as $categoria)

                <option
                    value="{{ $categoria->id }}"
                    @selected((string) request('categoria')===(string) $categoria->id)
                    >
                    {{ $categoria->nombre }}
                </option>

                @endforeach

            </select>

        </div>


        {{-- Feria --}}
        <div>

            <label
                for="feria"
                class="mb-1 block text-xs font-medium text-[#6B7A6C]">
                Feria
            </label>

            <select
                id="feria"
                name="feria"
                class="w-full rounded-xl border-[#DDE4D3]
                       bg-white px-3 py-2.5
                       text-sm text-[#1F4D22]
                       focus:border-paippa-green
                       focus:ring-paippa-green">

                <option value="">
                    Todas
                </option>

                @foreach ($ferias as $feria)

                <option
                    value="{{ $feria->id }}"
                    @selected((string) request('feria')===(string) $feria->id)
                    >
                    {{ $feria->nombre }}
                </option>

                @endforeach

            </select>

        </div>


        {{-- Precio máximo --}}
        <div>

            <label
                for="precio_max"
                class="mb-1 block text-xs font-medium text-[#6B7A6C]">
                Precio máximo
            </label>

            <input
                id="precio_max"
                type="number"
                name="precio_max"
                min="0"
                step="0.01"
                value="{{ request('precio_max') }}"
                placeholder="$ Máximo"
                class="w-full rounded-xl border-[#DDE4D3]
                       bg-white px-3 py-2.5
                       text-sm text-[#1F4D22]
                       placeholder:text-[#6B7A6C]
                       focus:border-paippa-green
                       focus:ring-paippa-green">

        </div>


        {{-- Acciones --}}
        <div class="flex items-end gap-2">

            <button
                type="submit"
                class="flex-1 rounded-xl
                       bg-paippa-green px-4 py-2.5
                       text-sm font-semibold text-white
                       transition hover:opacity-90">
                Aplicar
            </button>

            <a
                href="{{ route('catalogo') }}"
                class="rounded-xl border border-paippa-green
                       bg-white px-4 py-2.5
                       text-center text-sm font-semibold
                       text-paippa-green">
                Limpiar
            </a>

        </div>

    </div>

</form>