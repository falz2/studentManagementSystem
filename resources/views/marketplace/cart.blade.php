@extends('layouts.portal')

@section('page-title', 'Your cart')

@section('content')
    <div>
        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4A7C59]">Ready when you are</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight text-[#2B2B2B]">Your cart</h1>
        <p class="mt-2 text-sm text-[#68695f]">Review your items and place your order with local sellers.</p>
    </div>

        @if ($removedCount > 0)
            <p role="status" class="mt-5 rounded-[14px] border border-[#efcbbb] bg-[#fff1e8] px-4 py-3 text-sm font-bold text-[#7B3A2A]">A listing that was hidden, expired, or removed has been cleared from your cart.</p>
        @endif

    @if ($items->isEmpty())
        <div class="mt-7 rounded-[14px] border border-dashed border-[#D8CFBC] bg-white px-6 py-16 text-center">
            <span class="text-4xl" aria-hidden="true">🧺</span>
            <h2 class="mt-2 text-lg font-black text-[#2B2B2B]">Your cart is empty</h2>
            <p class="mt-2 text-sm text-[#68695f]">Browse poultry products and feed from farmers and suppliers.</p>
            <a href="{{ route('marketplace.index') }}" class="mt-5 inline-flex min-h-12 items-center rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Explore marketplace</a>
        </div>
    @else
        <div class="mt-7 grid items-start gap-6 xl:grid-cols-[1fr_360px]">
            <section class="overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
                <div class="divide-y divide-[#EEE8DB]">
                    @foreach ($items as $item)
                        <article class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                            <div class="min-w-0 flex-1">
                                <span class="text-xs font-extrabold uppercase tracking-wide text-[#4A7C59]">{{ $item['product']->category === 'feed' ? 'Poultry feed' : 'Poultry product' }}</span>
                                <h2 class="mt-1 font-black text-[#2B2B2B]">{{ $item['product']->name }}</h2>
                                <p class="mt-1 text-sm font-bold text-[#68695f]">{{ $item['product']->seller->name }} · UGX {{ number_format($item['product']->price) }} / {{ $item['product']->unit }}</p>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <form method="POST" action="{{ route('cart.update', $item['product']) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="sr-only" for="quantity-{{ $item['product']->id }}">Quantity</label>
                                    <input id="quantity-{{ $item['product']->id }}" name="quantity" type="number" value="{{ $item['quantity'] }}" min="1" max="{{ $item['product']->quantity }}" class="min-h-12 w-20 rounded-xl border-[#DED5C1] text-sm">
                                    <button class="min-h-12 px-2 text-xs font-extrabold text-[#315D3E] hover:underline">Update</button>
                                </form>
                                <span class="w-28 text-right text-sm font-black text-[#2B2B2B]">UGX {{ number_format($item['total']) }}</span>
                                <form method="POST" action="{{ route('cart.remove', $item['product']) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="min-h-12 px-2 text-xs font-extrabold text-[#9A4E32] hover:underline">Remove</button>
                                </form>
                            </div>
                            @unless ($item['available'])
                                <p class="text-sm font-extrabold text-[#9A4E32] sm:w-full">Only {{ $item['product']->quantity }} {{ \Illuminate\Support\Str::plural($item['product']->unit, $item['product']->quantity) }} remain. Update the quantity to continue.</p>
                            @endunless
                        </article>
                    @endforeach
                </div>
            </section>
            <section class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)] sm:p-6">
                <h2 class="text-lg font-black text-[#2B2B2B]">Order summary</h2>
                <div class="mt-5 flex justify-between border-t border-[#EEE8DB] pt-4 text-sm">
                    <span class="font-bold text-[#68695f]">Subtotal</span><span class="font-black text-[#2B2B2B]">UGX {{ number_format($total) }}</span>
                </div>
                <p class="mt-2 text-xs leading-5 text-[#68695f]">Your cart may include multiple sellers. Each seller will receive a separate order.</p>
                <form method="POST" action="{{ route('checkout.store') }}" class="mt-5">
                    @csrf
                    <label for="address_id" class="block text-xs font-extrabold text-[#575346]">Delivery address <span class="font-normal text-[#777263]">(optional)</span></label>
                    <select id="address_id" name="address_id" class="mt-2 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm">
                        <option value="">Choose a saved address</option>
                        @foreach ($addresses as $address)
                            <option value="{{ $address->id }}" @selected(old('address_id', $defaultAddressId) == $address->id)>{{ $address->label }} · {{ $address->formattedAddress() }}{{ $address->is_default ? ' · Default' : '' }}</option>
                        @endforeach
                    </select>
                    @error('address_id')<p class="mt-1 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    <a href="{{ route('addresses.index') }}" class="mt-1 inline-flex min-h-12 items-center text-xs font-extrabold text-[#315D3E]">＋ Manage saved addresses</a>
                    <label for="notes" class="block text-xs font-semibold text-slate-700">Order note <span class="font-normal text-slate-400">(optional)</span></label>
                    <textarea id="notes" name="notes" rows="3" maxlength="1000" placeholder="Add a note for the sellers" class="mt-2 block w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">{{ old('notes') }}</textarea>
                    @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-3 text-xs leading-5 text-[#68695f]">Placing an order does not charge a payment method. The seller will confirm your order.</p>
                    <button @disabled(! $canCheckout) class="mt-4 min-h-12 w-full rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c] disabled:cursor-not-allowed disabled:bg-[#d8d2c4]">Place order · UGX {{ number_format($total) }}</button>
                </form>
            </section>
        </div>
    @endif
@endsection
