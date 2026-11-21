<div class="grid min-h-screen w-full grid-cols-[minmax(0,0.7fr)_minmax(0,1.3fr)] bg-[#FDF6E3] sm:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)] lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)]">
    <section class="flex min-h-screen min-w-0 flex-col items-center justify-center bg-gradient-to-b from-[#3C444F] via-[#343C47] to-[#2D3542] px-3 py-6 text-center text-white sm:justify-between sm:px-8 sm:py-10 lg:items-start lg:px-12 lg:py-14 lg:text-left">
            {{ $logo }}
            <div class="mt-5 hidden max-w-sm sm:block">
                <h2 class="text-xl font-extrabold sm:text-2xl">Fresh poultry. Trusted local farms.</h2>
                <p class="mt-2 text-sm leading-6 text-white/90">Connect with farmers, buyers, and suppliers across Uganda in one place.</p>
            </div>
    </section>

    <section class="flex min-h-screen min-w-0 items-center justify-center px-3 py-6 sm:px-8 sm:py-10 lg:px-12 lg:py-14">
        <div class="w-full max-w-xl">
            {{ $slot }}
        </div>
    </section>
</div>
