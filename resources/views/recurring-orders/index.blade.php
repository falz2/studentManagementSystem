@extends('layouts.portal')

@section('page-title', 'Recurring supplier orders')

@section('content')
    <div>
        <p class="text-xs font-extrabold uppercase tracking-[0.16em] text-[#4A7C59]">Plan your feed supply</p>
        <h1 class="mt-2 text-3xl font-black text-[#2B2B2B]">Recurring supplier orders</h1>
        <p class="mt-2 text-sm leading-6 text-[#68695f]">Choose a feed item and schedule. PoultryHub creates a normal pending order on each due date; payment is not automated.</p>
    </div>

    <div class="mt-6 grid items-start gap-5 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="space-y-3" aria-label="Your recurring orders">
            @forelse ($recurringOrders as $schedule)
                <article class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_5px_18px_rgba(74,65,41,0.05)]">
                    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                        <div>
                            <div class="flex flex-wrap items-center gap-2"><h2 class="font-black">{{ $schedule->product->name }}</h2><span class="status-pill {{ $schedule->status === 'active' ? 'status-active' : 'status-neutral' }}">{{ str($schedule->status)->title() }}</span></div>
                            <p class="mt-1 text-sm text-[#68695f]">{{ $schedule->seller->name }} · {{ $schedule->quantity }} {{ \Illuminate\Support\Str::plural($schedule->product->unit, $schedule->quantity) }} · {{ str($schedule->frequency)->title() }}</p>
                            <p class="mt-1 text-sm font-extrabold text-[#315D3E]">Next order: {{ $schedule->next_run_at->format('M j, Y') }}</p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('recurring-orders.update', $schedule) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $schedule->status === 'active' ? 'paused' : 'active' }}">
                                <button class="min-h-12 rounded-xl border border-[#DED5C1] bg-white px-4 text-sm font-extrabold">{{ $schedule->status === 'active' ? 'Pause' : 'Resume' }}</button>
                            </form>
                            <form method="POST" action="{{ route('recurring-orders.destroy', $schedule) }}" onsubmit="return confirm('Remove this recurring order?')">
                                @csrf
                                @method('DELETE')
                                <button class="min-h-12 rounded-xl border border-[#EBCBC4] bg-[#FFF2EF] px-4 text-sm font-extrabold text-[#A52F25]">Remove</button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-[14px] border border-dashed border-[#D8CFBC] bg-[#FAF7EF] p-8 text-center">
                    <p class="text-4xl" aria-hidden="true">🔁</p>
                    <h2 class="mt-2 font-black">No repeating orders yet</h2>
                    <p class="mt-1 text-sm text-[#68695f]">Set a schedule for the poultry feed you regularly buy.</p>
                </div>
            @endforelse
            <div>{{ $recurringOrders->links() }}</div>
        </section>

        <section class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_5px_18px_rgba(74,65,41,0.05)]">
            <h2 class="text-lg font-black">Create a schedule</h2>
            @if ($products->isEmpty())
                <p class="mt-2 text-sm text-[#68695f]">There are no active supplier feed listings available right now.</p>
            @else
                <form method="POST" action="{{ route('recurring-orders.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="product_id" class="text-sm font-extrabold">Supplier feed</label>
                        <select id="product_id" name="product_id" required class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>{{ $product->name }} · {{ $product->seller->name }} · UGX {{ number_format($product->price) }}/{{ $product->unit }}</option>
                            @endforeach
                        </select>
                        @error('product_id')<p class="mt-1 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="quantity" class="text-sm font-extrabold">Quantity per order</label>
                        <input id="quantity" name="quantity" type="number" min="1" max="10000" value="{{ old('quantity', 1) }}" required class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                        @error('quantity')<p class="mt-1 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="frequency" class="text-sm font-extrabold">Repeat</label>
                        <select id="frequency" name="frequency" required class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                            <option value="weekly" @selected(old('frequency') === 'weekly')>Every week</option>
                            <option value="monthly" @selected(old('frequency', 'monthly') === 'monthly')>Every month</option>
                        </select>
                    </div>
                    <div>
                        <label for="next_run_at" class="text-sm font-extrabold">First order date</label>
                        <input id="next_run_at" name="next_run_at" type="date" min="{{ now()->addDay()->toDateString() }}" value="{{ old('next_run_at', now()->addWeek()->toDateString()) }}" required class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                        @error('next_run_at')<p class="mt-1 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    </div>
                    <button class="min-h-12 w-full rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Save recurring order</button>
                </form>
            @endif
        </section>
    </div>
@endsection
