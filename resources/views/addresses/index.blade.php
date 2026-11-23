@extends('layouts.portal')

@section('page-title', 'Delivery addresses')

@section('content')
    <div>
        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#4A7C59]">Saved for easy checkout</p>
        <h1 class="mt-2 text-3xl font-black text-[#2B2B2B]">Delivery addresses</h1>
        <p class="mt-2 text-sm text-[#68695f]">Keep your delivery details ready. Order totals are shown in UGX.</p>
    </div>

    <div class="mt-6 grid items-start gap-5 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="space-y-4" aria-label="Saved addresses">
            @forelse ($addresses as $address)
                <article class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-lg font-black">{{ $address->label }}</h2>
                        @if ($address->is_default)
                            <span class="rounded-full bg-[#eef4e8] px-3 py-1 text-xs font-extrabold text-[#315D3E]">Default address</span>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('addresses.update', $address) }}" class="grid gap-3 sm:grid-cols-2">
                        @csrf
                        @method('PUT')
                        @foreach (['label' => 'Address label', 'recipient_name' => 'Recipient name', 'phone' => 'Phone number', 'district' => 'District', 'subcounty' => 'County / subcounty', 'village' => 'Village / parish'] as $field => $label)
                            <div>
                                <label for="{{ $field }}-{{ $address->id }}" class="text-xs font-extrabold text-[#575346]">{{ $label }}</label>
                                <input id="{{ $field }}-{{ $address->id }}" name="{{ $field }}" value="{{ old($field, $address->$field) }}" required maxlength="255" class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                            </div>
                        @endforeach
                        <div class="sm:col-span-2">
                            <label for="instructions-{{ $address->id }}" class="text-xs font-extrabold text-[#575346]">Delivery instructions <span class="font-normal">(optional)</span></label>
                            <textarea id="instructions-{{ $address->id }}" name="instructions" rows="2" maxlength="1000" class="mt-1 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">{{ old('instructions', $address->instructions) }}</textarea>
                        </div>
                        <label class="flex min-h-12 items-center gap-2 text-sm font-bold text-[#575346] sm:col-span-2">
                            <input type="checkbox" name="is_default" value="1" @checked($address->is_default) class="rounded border-[#C9C1B1] text-[#4A7C59] focus:ring-[#4A7C59]">
                            Use as my default address
                        </label>
                        <button class="min-h-12 rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c] sm:col-span-2">Save address</button>
                    </form>
                    <div class="mt-3 flex flex-wrap gap-2 border-t border-[#EEE8DB] pt-3">
                        @unless ($address->is_default)
                            <form method="POST" action="{{ route('addresses.default', $address) }}">
                                @csrf
                                @method('PATCH')
                                <button class="min-h-12 rounded-xl border border-[#D8E4D9] bg-[#F1F6ED] px-4 text-sm font-extrabold text-[#315D3E]">Make default</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('addresses.destroy', $address) }}" onsubmit="return confirm('Remove this saved address?')">
                            @csrf
                            @method('DELETE')
                            <button class="min-h-12 rounded-xl border border-[#EBCBC4] bg-[#FFF2EF] px-4 text-sm font-extrabold text-[#A52F25]">Remove address</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-[14px] border border-dashed border-[#D8CFBC] bg-[#FAF7EF] p-8 text-center">
                    <p class="text-4xl" aria-hidden="true">📍</p>
                    <h2 class="mt-2 text-lg font-black">No saved addresses yet</h2>
                    <p class="mt-1 text-sm text-[#68695f]">Add a delivery location to make checkout quicker.</p>
                </div>
            @endforelse
        </section>

        <section class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
            <h2 class="text-lg font-black">Add an address</h2>
            <p class="mt-1 text-sm text-[#68695f]">Use a phone number and location the seller can find.</p>
            <form method="POST" action="{{ route('addresses.store') }}" class="mt-4 space-y-3">
                @csrf
                @foreach (['label' => 'Address label (Home, Farm, Work)', 'recipient_name' => 'Recipient name', 'phone' => 'Phone number', 'district' => 'District', 'subcounty' => 'County / subcounty', 'village' => 'Village / parish'] as $field => $label)
                    <div>
                        <label for="new-{{ $field }}" class="text-xs font-extrabold text-[#575346]">{{ $label }}</label>
                        <input id="new-{{ $field }}" name="{{ $field }}" value="{{ old($field) }}" required maxlength="255" class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                        @error($field)<p class="mt-1 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div>
                    <label for="new-instructions" class="text-xs font-extrabold text-[#575346]">Delivery instructions <span class="font-normal">(optional)</span></label>
                    <textarea id="new-instructions" name="instructions" rows="2" maxlength="1000" class="mt-1 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">{{ old('instructions') }}</textarea>
                </div>
                <label class="flex min-h-12 items-center gap-2 text-sm font-bold text-[#575346]">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default')) class="rounded border-[#C9C1B1] text-[#4A7C59] focus:ring-[#4A7C59]">
                    Make this my default address
                </label>
                <button class="min-h-12 w-full rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Save address</button>
            </form>
        </section>
    </div>
@endsection
