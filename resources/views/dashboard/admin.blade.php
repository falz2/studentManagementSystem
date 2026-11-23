@extends('layouts.portal')

@section('page-title', 'Platform overview')

@section('content')
    <div>
        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#3d7b3c]">PoultryHub administration</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Platform overview</h1>
        <p class="mt-2 text-sm text-slate-500">A live snapshot of marketplace activity and the community using PoultryHub.</p>
    </div>
    <section class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Platform statistics">
        @foreach ([['Products listed', $productCount], ['Farmers', $farmerCount], ['Feed suppliers', $supplierCount], ['Consumers', $consumerCount], ['Orders placed', $orderCount]] as [$label, $value])
            <article class="rounded-xl border border-slate-200 bg-white p-5"><p class="text-sm font-medium text-slate-500">{{ $label }}</p><p class="mt-4 text-3xl font-bold tracking-tight text-slate-900">{{ number_format($value) }}</p></article>
        @endforeach
    </section>
    <section class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="border-b border-slate-100 px-5 py-5 sm:px-6"><h2 class="text-base font-bold text-slate-900">Recent marketplace orders</h2><p class="mt-1 text-xs text-slate-500">Latest activity across the platform</p></div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-left">
                <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500"><tr><th class="px-6 py-3">Order</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Seller</th><th class="px-4 py-3">Total</th><th class="px-6 py-3">Status</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse ($orders as $order)
                        <tr><td class="whitespace-nowrap px-6 py-4 font-semibold text-slate-700">{{ $order->order_number }}</td><td class="px-4 py-4 text-slate-600">{{ $order->buyer->name }}</td><td class="px-4 py-4 text-slate-600">{{ $order->seller?->name ?? 'Account unavailable' }}</td><td class="whitespace-nowrap px-4 py-4 font-medium text-slate-700">UGX {{ number_format($order->total) }}</td><td class="px-6 py-4"><span class="status-pill {{ $order->status === 'completed' ? 'status-active' : ($order->status === 'pending' ? 'status-pending' : 'status-neutral') }}">{{ str($order->status)->title() }}</span></td></tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-10 text-center text-sm text-slate-500">Marketplace orders will appear here.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
