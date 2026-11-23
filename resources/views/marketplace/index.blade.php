@extends('layouts.portal')

@section('page-title', 'Marketplace')

@section('content')
    <section class="relative overflow-hidden rounded-2xl border border-[#e8dfcf] bg-[#4A7C59] shadow-[0_12px_30px_rgba(74,124,89,0.12)]">
        <img src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=1600&q=82" alt="Poultry flock at a local farm" class="absolute inset-0 h-full w-full object-cover opacity-40">
        <div class="absolute inset-0 bg-gradient-to-r from-[#34362f]/90 via-[#4A7C59]/75 to-transparent"></div>
        <div class="relative flex min-h-[240px] flex-col justify-between gap-6 p-6 sm:p-8 lg:flex-row lg:items-end">
            <div class="max-w-xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#FDF6E3]">Direct from Ugandan farmers & suppliers</p>
                <h1 class="mt-3 text-3xl font-extrabold tracking-tight text-white sm:text-4xl">Good food starts close to home.</h1>
                <p class="mt-3 max-w-lg text-sm leading-6 text-white/90">Find fresh poultry products and reliable feed, with transparent prices from trusted local sellers.</p>
            </div>
            @if (in_array($role, ['farmer', 'supplier'], true))
                <a href="{{ route('seller.products.create') }}" class="inline-flex min-h-12 shrink-0 items-center justify-center rounded-xl bg-[#4A7C59] px-5 text-sm font-extrabold text-white shadow-[0_8px_18px_rgba(52,54,47,0.18)] transition hover:bg-[#3f6b4c]">List a product</a>
            @endif
        </div>
    </section>

    <form method="GET" action="{{ route('marketplace.index') }}" class="mt-7 grid gap-3 rounded-[14px] border border-[#e8dfcf] bg-white/95 p-4 shadow-[0_8px_24px_rgba(91,74,50,0.06)] sm:grid-cols-[1fr_190px_auto]">
        <label class="sr-only" for="search">Search products</label>
        <input id="search" name="search" value="{{ request('search') }}" placeholder="Search chicken, eggs, feed..." class="min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
        <label class="sr-only" for="category">Category</label>
        <select id="category" name="category" class="min-h-12 rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
            <option value="">All categories</option>
            <option value="poultry" @selected(request('category') === 'poultry')>Poultry products</option>
            <option value="feed" @selected(request('category') === 'feed')>Poultry feed</option>
        </select>
        <button class="min-h-12 rounded-xl bg-[#4A7C59] px-5 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Search</button>
    </form>

    <div class="mt-7 flex items-center justify-between">
        <h2 class="text-lg font-black text-[#2B2B2B]">Available products</h2>
        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-500">{{ $products->total() }} listings</span>
            <a href="{{ route('marketplace.map') }}" class="inline-flex min-h-12 items-center text-xs font-extrabold text-[#315D3E] hover:underline">🗺️ Farm map</a>
        </div>
    </div>
    <div class="mt-4 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($products as $product)
            <article class="overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white/95 shadow-[0_6px_20px_rgba(91,74,50,0.06)] transition hover:-translate-y-1 hover:shadow-[0_14px_28px_rgba(91,74,50,0.12)]">
                <a href="{{ route('marketplace.show', $product) }}" class="block">
                    @if ($product->image_path)
                        <img loading="lazy" decoding="async" src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-48 w-full object-cover">
                    @else
                        @if ($product->category === 'feed')
                            <div class="relative h-48 overflow-hidden bg-[#d9c8a5]">
                                <img loading="lazy" decoding="async" src="https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=900&q=72" alt="Poultry feed" class="h-full w-full object-cover opacity-75">
                                <span class="absolute bottom-3 left-3 rounded-full bg-[#fff8e8]/95 px-3 py-1 text-xs font-bold text-[#34362f]">Local feed supply</span>
                            </div>
                        @else
                            <div class="relative h-48 overflow-hidden bg-[#4A7C59]">
                                <img loading="lazy" decoding="async" src="https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=900&q=72" alt="Fresh poultry from a local farm" class="h-full w-full object-cover opacity-90">
                                <span class="absolute bottom-3 left-3 rounded-full bg-[#fff8e8]/95 px-3 py-1 text-xs font-bold text-[#34362f]">Farm fresh</span>
                            </div>
                        @endif
                    @endif
                </a>
                <div class="p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="rounded-full {{ $product->category === 'feed' ? 'bg-[#FFF3D7] text-[#805E14]' : 'bg-[#eef4e8] text-[#315D3E]' }} px-3 py-1.5 text-xs font-extrabold uppercase tracking-wide">{{ $product->category === 'feed' ? 'Poultry feed' : 'Poultry product' }}</span>
                        <span class="text-xs font-bold text-[#68695f]">{{ $product->quantity }} {{ \Illuminate\Support\Str::plural($product->unit, $product->quantity) }} left</span>
                    </div>
                    <a href="{{ route('marketplace.show', $product) }}" class="mt-3 block text-lg font-black text-[#2B2B2B] hover:text-[#315D3E]">{{ $product->name }}</a>
                    <p class="mt-1 text-sm font-bold text-[#68695f]">From {{ $product->seller->name }}</p>
                    <p class="mt-2 text-sm font-extrabold text-[#575346]">
                        @if ($product->reviews_count > 0)
                            ⭐ {{ number_format((float) $product->reviews_avg_rating, 1) }} <span class="font-semibold text-[#68695f]">({{ $product->reviews_count }} reviews)</span>
                        @else
                            New listing · Be the first to review
                        @endif
                    </p>
                    <div class="mt-4 flex items-end justify-between border-t border-[#EEE8DB] pt-4">
                        <p class="text-lg font-black text-[#2B2B2B]">UGX {{ number_format($product->price) }}<span class="ml-1 text-xs font-bold text-[#68695f]">/ {{ $product->unit }}</span></p>
                        @if ($product->quantity <= $product->low_stock_threshold)
                            <span class="text-xs font-extrabold text-[#9A4E32]">Only {{ $product->quantity }} left</span>
                        @endif
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('marketplace.show', $product) }}" class="flex min-h-12 items-center justify-center rounded-xl border border-[#D8E4D9] bg-[#F1F6ED] px-3 text-center text-xs font-extrabold text-[#315D3E]">View details</a>
                        <form method="POST" action="{{ route('cart.add', $product) }}">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button class="min-h-12 w-full rounded-xl bg-[#4A7C59] px-3 text-xs font-extrabold text-white hover:bg-[#3f6b4c]">＋ Add to cart</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center sm:col-span-2 xl:col-span-3">
                <h3 class="font-semibold text-slate-800">No listings match your search</h3>
                <p class="mt-1 text-sm text-slate-500">Try another product name or category.</p>
                <a href="{{ route('marketplace.index') }}" class="mt-4 inline-flex text-sm font-semibold text-[#3d7b3c] hover:underline">Clear filters</a>
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
