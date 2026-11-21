<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-[#2B2B2B]">Welcome back</h1>
            <p class="mt-1 text-sm text-[#68695f]">Sign in to continue to your PoultryHub account.</p>
        </div>

        <x-validation-errors class="mb-4" />

        @session('status')
            <div class="mb-4 font-medium text-sm text-green-600">
                {{ $value }}
            </div>
        @endsession

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div>
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            </div>

            <div class="block mt-4">
                <label for="remember_me" class="flex items-center">
                    <x-checkbox id="remember_me" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
                </label>
            </div>

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                @if (Route::has('password.request'))
                    <a class="underline text-sm text-[#68695f] hover:text-[#C96F4A] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C96F4A]" href="{{ route('password.request') }}">
                        {{ __('Forgot your password?') }}
                    </a>
                @endif

                <x-button>
                    {{ __('Log in') }}
                </x-button>
            </div>
        </form>

        @if (Route::has('register'))
            <p class="mt-6 border-t border-slate-100 pt-4 text-center text-sm text-slate-500">
                New to PoultryHub?
                <a class="font-bold text-[#C96F4A] hover:text-[#b86140]" href="{{ route('register') }}">Create an account</a>
            </p>
        @endif
    </x-authentication-card>
</x-guest-layout>
