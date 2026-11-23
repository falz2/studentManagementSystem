@extends('layouts.portal')

@section('page-title', 'Seller analytics')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div><p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4A7C59]">Business insights</p><h1 class="mt-2 text-3xl font-black tracking-tight text-[#2B2B2B]">Sales analytics</h1><p class="mt-2 text-sm text-[#68695f]">Compare completed sales, product performance, and category revenue over time.</p></div>
        <a href="{{ route('seller.dashboard') }}" class="inline-flex min-h-12 items-center text-sm font-extrabold text-[#315D3E] hover:underline">← Seller overview</a>
    </div>

    <form method="GET" action="{{ route('seller.analytics') }}" class="mt-5 flex flex-wrap items-end gap-3 rounded-[14px] border border-[#e8dfcf] bg-white p-4 shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
        <div>
            <label for="days" class="block text-xs font-extrabold text-[#575346]">Report period</label>
            <select id="days" name="days" class="mt-1 min-h-12 rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                @foreach ([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $value => $label)
                    <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button class="min-h-12 rounded-xl bg-[#4A7C59] px-5 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Update report</button>
    </form>

    <section class="mt-7 grid gap-4 sm:grid-cols-3">
        <article class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)]"><p class="text-sm font-bold text-[#68695f]">Revenue · last {{ $days }} days</p><p class="mt-3 text-2xl font-black text-[#2B2B2B]">UGX {{ number_format($periodRevenue) }}</p><p class="mt-1 text-xs font-bold text-[#777263]">From completed orders</p></article>
        <article class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)]"><p class="text-sm font-bold text-[#68695f]">Total orders</p><p class="mt-3 text-2xl font-black text-[#2B2B2B]">{{ $orderCount }}</p><p class="mt-1 text-xs font-bold text-[#777263]">All order statuses</p></article>
        <article class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)]"><p class="text-sm font-bold text-[#68695f]">Active listings</p><p class="mt-3 text-2xl font-black text-[#2B2B2B]">{{ $activeProductCount }}</p><p class="mt-1 text-xs font-bold text-[#777263]">Of {{ $productCount }} total products</p></article>
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="text-base font-black text-[#2B2B2B]">Daily sales · last {{ $days }} days</h2>
            <p class="mt-1 text-xs text-slate-500">Revenue from completed orders (UGX)</p>
            <div class="mt-8 border-b border-l border-slate-100 px-1 pt-3">@include('components.sales-trend-chart')</div>
            <div class="mt-2 flex justify-between text-[10px] text-[#777263]"><span>{{ now()->subDays($days - 1)->format('M j') }}</span><span>{{ now()->format('M j') }}</span></div>
        </section>
        <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
            <h2 class="text-base font-bold text-slate-900">Revenue by category</h2>
            <p class="mt-1 text-xs text-slate-500">Compare poultry products and feed sales</p>
            @php($poultryRevenue = (int) ($categoryRevenue->get('poultry') ?? 0))
            @php($feedRevenue = (int) ($categoryRevenue->get('feed') ?? 0))
            @php($categoryTotal = $poultryRevenue + $feedRevenue)
            @php($poultryPercent = $categoryTotal > 0 ? $poultryRevenue / $categoryTotal * 100 : 0)
            @php($feedPercent = $categoryTotal > 0 ? $feedRevenue / $categoryTotal * 100 : 0)
            <div class="mt-6 flex flex-col items-center gap-6 sm:flex-row">
                <svg viewBox="0 0 120 120" role="img" aria-label="Revenue split by poultry product and feed category" class="h-36 w-36 shrink-0 -rotate-90">
                    <circle cx="60" cy="60" r="44" fill="none" stroke="#f1f5f9" stroke-width="18"/>
                    <circle cx="60" cy="60" r="44" pathLength="100" fill="none" stroke="#4A7C59" stroke-width="18" stroke-dasharray="{{ $poultryPercent }} {{ 100 - $poultryPercent }}"/>
                    <circle cx="60" cy="60" r="44" pathLength="100" fill="none" stroke="#C96F4A" stroke-width="18" stroke-dasharray="{{ $feedPercent }} {{ 100 - $feedPercent }}" stroke-dashoffset="-{{ $poultryPercent }}"/>
                </svg>
                <div class="w-full space-y-4">
                @foreach (['poultry' => 'Poultry products', 'feed' => 'Poultry feed'] as $category => $label)
                    @php($amount = (int) ($categoryRevenue->get($category) ?? 0))
                    <div class="flex justify-between gap-3 text-xs"><span class="flex items-center gap-2 font-bold text-[#575346]"><span class="h-2.5 w-2.5 rounded-full {{ $category === 'feed' ? 'bg-[#C96F4A]' : 'bg-[#4A7C59]' }}"></span>{{ $label }}</span><span class="text-[#68695f]">UGX {{ number_format($amount) }}</span></div>
                @endforeach
                <p class="border-t border-slate-100 pt-3 text-xs font-semibold text-slate-700">Total: UGX {{ number_format($categoryTotal) }}</p>
                </div>
            </div>
        </section>
    </div>

    <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
        <div class="flex items-start justify-between"><div><h2 class="text-base font-bold text-slate-900">Top-selling products</h2><p class="mt-1 text-xs text-slate-500">Ranked by completed sales value</p></div><a href="{{ route('seller.products.index') }}" class="text-xs font-semibold text-[#3d7b3c]">Manage products →</a></div>
        <div class="mt-5 divide-y divide-slate-100">
            @forelse ($topProducts as $product)
                <div class="flex items-center justify-between gap-4 py-3"><div><p class="text-sm font-semibold text-slate-800">{{ $product->name }}</p><p class="mt-1 text-xs capitalize text-slate-500">{{ $product->category === 'feed' ? 'Poultry feed' : 'Poultry product' }} · {{ $product->quantity }} {{ \Illuminate\Support\Str::plural($product->unit, $product->quantity) }} in stock</p></div><span class="whitespace-nowrap text-sm font-semibold text-slate-700">UGX {{ number_format($product->sales_total ?? 0) }}</span></div>
            @empty
                <p class="py-8 text-center text-sm text-slate-500">Completed sales will appear in your analytics.</p>
            @endforelse
        </div>
    </section>
@endsection
