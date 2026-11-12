<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-verde-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-verde-700 focus:bg-verde-700 active:bg-verde-800 focus:outline-none focus:ring-2 focus:ring-verde-500 focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
