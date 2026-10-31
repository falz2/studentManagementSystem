<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-[#2563eb] border border-transparent rounded-lg font-semibold text-xs text-white tracking-wide hover:bg-[#1d4ed8] focus:bg-[#1d4ed8] active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
