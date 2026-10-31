<div class="min-h-screen flex flex-col sm:justify-center items-center px-4 py-10 sm:pt-0 bg-slate-50">
    <div class="mb-6">
        {{ $logo }}
    </div>

    <div class="mb-3 text-center">
        <h1 class="text-xl font-bold text-slate-900">Welcome to Northstar</h1>
        <p class="mt-1 text-sm text-slate-500">Sign in to your university account</p>
    </div>
    <div class="w-full sm:max-w-md px-6 py-7 bg-white border border-slate-200 shadow-sm overflow-hidden rounded-xl">
        {{ $slot }}
    </div>
</div>
