@extends('layouts.portal')

@section('page-title', 'My products')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4A7C59]">Seller workspace</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-[#2B2B2B]">My products</h1>
            <p class="mt-2 text-sm text-[#68695f]">Keep your listings, prices and available quantities up to date.</p>
        </div>
        <a href="{{ route('seller.products.create') }}" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">＋ Add a product</a>
    </div>

    @if ($lowStockCount > 0)
        <div role="status" class="mt-5 rounded-[14px] border border-[#efcbbb] bg-[#fff1e8] px-4 py-3 text-sm font-extrabold text-[#7B3A2A]">⚠ {{ $lowStockCount }} listing(s) are at or below their low-stock alert level.</div>
    @endif
    @if ($expiringProducts->isNotEmpty())
        <div role="status" class="mt-3 rounded-[14px] border border-[#f1ddb0] bg-[#FFF5D9] px-4 py-3 text-sm font-extrabold text-[#624B1C]">🗓️ Check expiry dates: {{ $expiringProducts->pluck('name')->join(', ') }}.</div>
    @endif

    <div class="mt-7 overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left">
                <thead class="bg-[#F6F2E8] text-xs font-extrabold uppercase tracking-[0.1em] text-[#575346]"><tr><th class="px-5 py-3">Product</th><th class="px-4 py-3">Category</th><th class="px-4 py-3">Price</th><th class="px-4 py-3">Stock / alert</th><th class="px-4 py-3">Expiry</th><th class="px-4 py-3">Status</th><th class="px-5 py-3">Actions</th></tr></thead>
                <tbody class="divide-y divide-[#EEE8DB] text-sm">
                    @forelse ($products as $product)
                        <tr>
                            <td class="px-5 py-4"><a href="{{ route('marketplace.show', $product) }}" class="font-extrabold text-[#2B2B2B] hover:text-[#315D3E]">{{ $product->name }}</a></td>
                            <td class="px-4 py-4 text-[#575346]">{{ $product->category === 'feed' ? 'Poultry feed' : 'Poultry' }}</td>
                            <td class="whitespace-nowrap px-4 py-4 font-extrabold text-[#2B2B2B]">UGX {{ number_format($product->price) }} / {{ $product->unit }}</td>
                            <td class="px-4 py-4"><span class="font-extrabold text-[#2B2B2B]">{{ $product->quantity }}</span><span class="mt-1 block text-xs text-[#68695f]">Alert at {{ $product->low_stock_threshold }}</span></td>
                            <td class="whitespace-nowrap px-4 py-4 text-xs font-bold {{ $product->isExpired() ? 'text-[#9A4E32]' : 'text-[#68695f]' }}">{{ $product->expires_at?->format('M j, Y') ?? 'Not set' }}{{ $product->isExpired() ? ' · Expired' : '' }}</td>
                            <td class="px-4 py-4"><span class="status-pill {{ $product->status === 'active' ? 'status-active' : 'status-neutral' }}">{{ $product->status === 'active' ? 'Active' : 'Hidden' }}</span></td>
                            <td class="px-5 py-4"><div class="flex items-center gap-3"><a href="{{ route('seller.products.edit', $product) }}" class="min-h-12 inline-flex items-center text-xs font-extrabold text-[#315D3E] hover:underline">Edit</a><form method="POST" action="{{ route('seller.products.destroy', $product) }}" onsubmit="return confirm('Remove this product from the marketplace?')">@csrf @method('DELETE')<button class="min-h-12 text-xs font-extrabold text-[#9A4E32] hover:underline">Delete</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-14 text-center"><p class="font-black text-[#2B2B2B]">You have no product listings yet</p><p class="mt-1 text-sm text-[#68695f]">Add your first listing to reach customers directly.</p><a href="{{ route('seller.products.create') }}" class="mt-4 inline-flex min-h-12 items-center text-sm font-extrabold text-[#315D3E] hover:underline">Create a listing →</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@endsection
