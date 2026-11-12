{{--
    Tabla del panel: en escritorio se ve como tabla y en el celular cada fila
    se apila como una tarjeta (cada <td> usa data-etiqueta para su título).
--}}
@props(['columnas'])

<div class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-gray-200">
    <table class="tabla-panel min-w-full divide-y divide-gray-200 text-sm">
        <thead class="hidden bg-gray-50 md:table-header-group">
            <tr>
                @foreach ($columnas as $columna)
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-700">{{ $columna }}</th>
                @endforeach
                <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-700"><span class="sr-only">Acciones</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            {{ $slot }}
        </tbody>
    </table>
</div>
