<x-guest-layout>
    <x-authentication-card>
        <x-slot name="logo">
            <x-authentication-card-logo />
        </x-slot>

        <div class="mb-6">
            <h1 class="text-2xl font-extrabold text-[#2B2B2B]">Create your account</h1>
            <p class="mt-1 text-sm text-[#68695f]">Join local farmers, buyers, and suppliers on PoultryHub.</p>
        </div>

        <x-validation-errors class="mb-4" />

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div>
                <x-label for="name" value="{{ __('Name') }}" />
                <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            </div>

            <div class="mt-4">
                <x-label for="email" value="{{ __('Email') }}" />
                <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            </div>

            <div class="mt-4">
                <x-label for="role" value="{{ __('I am joining as') }}" />
                <select id="role" name="role" required class="block mt-1 w-full rounded-xl border-[#dcd2be] shadow-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                    <option value="consumer" @selected(old('role', 'consumer') === 'consumer')>Consumer — shop poultry and feed</option>
                    <option value="farmer" @selected(old('role') === 'farmer')>Farmer — sell poultry products</option>
                    <option value="supplier" @selected(old('role') === 'supplier')>Feed supplier — list poultry feed</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-4">
                <x-label for="password" value="{{ __('Password') }}" />
                <x-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            </div>

            <div class="mt-4">
                <x-label for="password_confirmation" value="{{ __('Confirm Password') }}" />
                <x-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            </div>

            @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
                <div class="mt-4">
                    <x-label for="terms">
                        <div class="flex items-center">
                            <x-checkbox name="terms" id="terms" required />

                            <div class="ms-2">
                                {!! __('I agree to the :terms_of_service and :privacy_policy', [
                                        'terms_of_service' => '<a target="_blank" href="'.route('terms.show').'" class="underline text-sm text-[#68695f] hover:text-[#C96F4A] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C96F4A]">'.__('Terms of Service').'</a>',
                                        'privacy_policy' => '<a target="_blank" href="'.route('policy.show').'" class="underline text-sm text-[#68695f] hover:text-[#C96F4A] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C96F4A]">'.__('Privacy Policy').'</a>',
                                ]) !!}
                            </div>
                        </div>
                    </x-label>
                </div>
            @endif

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                <a class="underline text-sm text-[#68695f] hover:text-[#C96F4A] rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C96F4A]" href="{{ route('login') }}">
                    {{ __('Already registered?') }}
                </a>

                <x-button>
                    {{ __('Register') }}
                </x-button>
            </div>
        </form>
    </x-authentication-card>
</x-guest-layout>
