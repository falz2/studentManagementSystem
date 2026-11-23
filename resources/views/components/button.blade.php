<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-12 items-center px-4 py-3 bg-[#4A7C59] border border-transparent rounded-xl font-extrabold text-sm text-white tracking-wide shadow-[0_6px_16px_rgba(74,124,89,0.18)] hover:bg-[#3f6b4c] focus:bg-[#3f6b4c] active:bg-[#315D3E] focus:outline-none focus:ring-2 focus:ring-[#4A7C59] focus:ring-offset-2 disabled:opacity-50 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
