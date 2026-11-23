@extends('layouts.portal')

@section('page-title', 'Seller dashboard')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4A7C59]">{{ $role === 'supplier' ? 'Feed supplier workspace' : 'Farmer workspace' }}</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-[#2B2B2B]">Welcome, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-sm text-[#68695f]">Manage your listings, customer orders and business performance.</p>
        </div>
        <a href="{{ route('seller.products.create') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">＋ Add a product</a>
    </div>

    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Seller statistics">
        @foreach ([['Products listed', $productCount, $activeProductCount.' visible on marketplace'], ['Orders received', $orderCount, 'Across your listings'], ['Awaiting confirmation', $pendingCount, 'Customer orders'], ['Completed sales', 'UGX '.number_format($revenue), 'Revenue from completed orders']] as [$label, $value, $detail])
            <article class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
                <p class="text-sm font-bold text-[#68695f]">{{ $label }}</p>
                <p class="mt-4 truncate text-2xl font-black tracking-tight text-[#2B2B2B]">{{ $value }}</p>
                <p class="mt-1 text-xs font-bold text-[#777263]">{{ $detail }}</p>
            </article>
        @endforeach
    </section>

    @if ($lowStockProducts->isNotEmpty() || $expiringProducts->isNotEmpty())
        <section class="mt-5 grid gap-3 lg:grid-cols-2" aria-label="Inventory alerts">
            @if ($lowStockProducts->isNotEmpty())
                <div class="rounded-[14px] border border-[#efcbbb] bg-[#fff1e8] p-4">
                    <h2 class="font-black text-[#7B3A2A]">⚠️ Low stock · {{ $lowStockProducts->count() }} listing(s)</h2>
                    <ul class="mt-2 space-y-2 text-sm font-bold text-[#7B3A2A]">
                        @foreach ($lowStockProducts as $product)
                            <li class="flex items-center justify-between gap-3"><span>{{ $product->name }} · {{ $product->quantity }} {{ $product->unit }} left</span><a href="{{ route('seller.products.edit', $product) }}" class="min-h-12 inline-flex items-center underline">Update</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if ($expiringProducts->isNotEmpty())
                <div class="rounded-[14px] border border-[#f1ddb0] bg-[#FFF5D9] p-4">
                    <h2 class="font-black text-[#624B1C]">🗓️ Expiry dates to check</h2>
                    <ul class="mt-2 space-y-2 text-sm font-bold text-[#624B1C]">
                        @foreach ($expiringProducts as $product)
                            <li class="flex items-center justify-between gap-3"><span>{{ $product->name }} · {{ $product->expires_at->format('M j, Y') }}</span><a href="{{ route('seller.products.edit', $product) }}" class="min-h-12 inline-flex items-center underline">Update</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </section>
    @endif

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <section class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)] sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <div><h2 class="text-base font-black text-[#2B2B2B]">Sales in the last 30 days</h2><p class="mt-1 text-xs font-bold text-[#68695f]">Completed order revenue · UGX</p></div>
                <a href="{{ route('seller.analytics') }}" class="min-h-12 inline-flex items-center text-xs font-extrabold text-[#315D3E] hover:underline">Full analytics →</a>
            </div>
            <div class="mt-7 border-b border-l border-slate-100 px-1 pt-3">@include('components.sales-trend-chart')</div>
            <div class="mt-2 flex justify-between text-[10px] text-[#777263]"><span>{{ now()->subDays(29)->format('M j') }}</span><span>{{ now()->format('M j') }}</span></div>
        </section>
        <section class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,41,0.06)] sm:p-6">
            <div class="flex items-start justify-between"><div><h2 class="text-base font-black text-[#2B2B2B]">Best-selling products</h2><p class="mt-1 text-xs font-bold text-[#68695f]">By completed sales value</p></div><a href="{{ route('seller.products.index') }}" class="min-h-12 inline-flex items-center text-xs font-extrabold text-[#315D3E]">All products</a></div>
            <div class="mt-5 space-y-4">
                @forelse ($topProducts as $product)
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0"><p class="truncate text-sm font-extrabold text-[#2B2B2B]">{{ $product->name }}</p><p class="mt-1 text-xs text-[#68695f]">{{ $product->category === 'feed' ? 'Poultry feed' : 'Poultry product' }}</p></div>
                        <span class="shrink-0 text-xs font-extrabold text-[#2B2B2B]">UGX {{ number_format($product->sales_total ?? 0) }}</span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-slate-500">Completed sales will show here.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="mt-6 overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
        <div class="flex items-center justify-between border-b border-[#EEE8DB] px-5 py-5 sm:px-6">
            <div><h2 class="text-base font-black text-[#2B2B2B]">Recent orders</h2><p class="mt-1 text-xs font-bold text-[#68695f]">Updates from customers</p></div>
            <a href="{{ route('seller.orders') }}" class="min-h-12 inline-flex items-center text-xs font-extrabold text-[#315D3E]">View all →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left">
                <thead class="bg-[#F6F2E8] text-xs font-extrabold uppercase tracking-[0.1em] text-[#575346]"><tr><th class="px-6 py-3">Order</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Amount</th><th class="px-6 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-[#EEE8DB] text-xs">
                    @forelse ($orders as $order)
                        <tr><td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-700">{{ $order->order_number }}</td><td class="px-4 py-4 text-slate-600">{{ $order->buyer->name }}</td><td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $order->created_at->format('M d, Y') }}</td><td class="whitespace-nowrap px-4 py-4 font-medium text-slate-700">UGX {{ number_format($order->total) }}</td><td class="px-6 py-4"><span class="status-pill {{ $order->status === 'completed' ? 'status-active' : ($order->status === 'pending' ? 'status-pending' : 'status-neutral') }}">{{ str($order->status)->title() }}</span></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Orders placed on your listings will appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
