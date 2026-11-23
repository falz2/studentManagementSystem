@extends('layouts.portal')

@section('page-title', $sellerView ? 'Orders received' : 'My orders')

@section('content')
    <div>
        <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4A7C59]">{{ $sellerView ? 'Seller workspace' : 'Order history' }}</p>
        <h1 class="mt-2 text-3xl font-black tracking-tight text-[#2B2B2B]">{{ $sellerView ? 'Orders received' : 'My orders' }}</h1>
        <p class="mt-2 text-sm text-[#68695f]">{{ $sellerView ? 'Review incoming customer orders and keep each order status up to date.' : 'Track delivery progress and contact the seller about your purchase.' }}</p>
    </div>

    @if ($sellerView && session('delivery_share_url'))
        <section class="mt-5 rounded-[14px] border border-[#cfe0cf] bg-[#eef4e8] p-4" aria-label="Private rider tracking link">
            <h2 class="font-black text-[#315D3E]">Private rider tracking link</h2>
            <p class="mt-1 text-sm text-[#315D3E]">Send this link only to the rider for this order. It expires in 12 hours or when delivery ends.</p>
            <div class="mt-3 flex flex-col gap-2 sm:flex-row">
                <label for="delivery-share-url" class="sr-only">Rider tracking link</label>
                <input id="delivery-share-url" data-copy-fallback type="url" readonly value="{{ session('delivery_share_url') }}" class="min-h-12 min-w-0 flex-1 rounded-xl border-[#cfe0cf] bg-white text-xs text-[#2B2B2B]">
                <button type="button" data-copy-value="{{ session('delivery_share_url') }}" class="min-h-12 rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Copy rider link</button>
            </div>
        </section>
    @endif

    <div class="mt-7 space-y-4">
        @forelse ($orders as $order)
            <article class="overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
                <div class="flex flex-col justify-between gap-3 border-b border-[#EEE8DB] bg-[#F6F2E8] px-5 py-4 sm:flex-row sm:items-center">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span class="text-sm font-black text-[#2B2B2B]">{{ $order->order_number }}</span>
                        <span class="text-xs font-bold text-[#68695f]">{{ $order->created_at->format('M d, Y · g:i A') }}</span>
                        @if ($sellerView)
                            <span class="text-xs font-bold text-[#68695f]">From {{ $order->buyer->name }}</span>
                        @else
                            <span class="text-xs font-bold text-[#68695f]">Seller: {{ $order->seller?->name ?? 'Seller account unavailable' }}</span>
                        @endif
                    </div>
                    <span class="status-pill {{ $order->status === 'completed' ? 'status-active' : ($order->status === 'pending' ? 'status-pending' : 'status-neutral') }}">{{ str($order->status)->title() }}</span>
                </div>
                <div class="divide-y divide-[#EEE8DB] px-5">
                    @foreach ($order->items as $item)
                        <div class="flex items-center justify-between gap-4 py-3 text-sm">
                            <div><p class="font-extrabold text-[#2B2B2B]">{{ $item->product_name }}</p><p class="mt-1 text-xs text-[#68695f]">{{ $item->quantity }} {{ \Illuminate\Support\Str::plural($item->unit, $item->quantity) }} × UGX {{ number_format($item->unit_price) }}</p></div>
                            <span class="whitespace-nowrap font-extrabold text-[#2B2B2B]">UGX {{ number_format($item->total) }}</span>
                        </div>
                    @endforeach
                </div>
                <section class="border-t border-[#EEE8DB] px-5 py-4" aria-label="Delivery tracking for {{ $order->order_number }}">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <h2 class="text-sm font-black text-[#2B2B2B]">Delivery tracking</h2>
                            <p class="mt-1 text-xs font-bold text-[#68695f]">
                                @if ($order->delivery_status === 'in_transit')
                                    🚚 On the way
                                @elseif ($order->delivery_status === 'delivered')
                                    ✅ Delivered {{ $order->delivered_at?->format('M j, g:i A') }}
                                @elseif ($order->estimated_delivery_at)
                                    Estimated {{ $order->estimated_delivery_at->format('M j, g:i A') }}
                                @elseif ($order->delivery_status === 'scheduled')
                                    Delivery scheduled
                                @else
                                    Delivery has not been scheduled yet
                                @endif
                            </p>
                        </div>
                        @if ($order->delivery_phone || $order->delivery_address)
                            <div class="text-xs text-[#68695f]">
                                @if ($order->recipient_name)<p class="font-extrabold text-[#2B2B2B]">{{ $order->recipient_name }}</p>@endif
                                @if ($order->delivery_phone)<p>{{ $order->delivery_phone }}</p>@endif
                                @if ($order->delivery_address)<p>{{ $order->delivery_address }}</p>@endif
                                @if ($order->delivery_instructions)<p class="mt-1">Note: {{ $order->delivery_instructions }}</p>@endif
                            </div>
                        @endif
                    </div>
                    <ol class="mt-4 grid grid-cols-2 gap-3 text-xs sm:grid-cols-4">
                        @foreach ([
                            ['Placed', $order->created_at, true],
                            ['Confirmed', $order->confirmed_at, $order->confirmed_at !== null],
                            ['On the way', $order->in_transit_at, $order->in_transit_at !== null],
                            ['Delivered', $order->delivered_at, $order->delivered_at !== null],
                        ] as [$step, $timestamp, $complete])
                            <li class="rounded-xl border {{ $complete ? 'border-[#cfe0cf] bg-[#eef4e8] text-[#315D3E]' : 'border-[#EEE8DB] bg-[#FFFCF4] text-[#777263]' }} px-3 py-2">
                                <span class="block font-extrabold">{{ $complete ? '✓ ' : '○ ' }}{{ $step }}</span>
                                <span class="mt-1 block">{{ $timestamp?->format('M j · g:i A') ?? 'Waiting' }}</span>
                            </li>
                        @endforeach
                    </ol>
                    @if ($order->delivery_status === 'in_transit' && $order->deliveryShare?->isActive())
                        <div class="mt-4">
                            <p id="delivery-location-status-{{ $order->id }}" role="status" aria-live="polite" class="mb-2 text-xs font-bold text-[#315D3E]">Connecting to live delivery location…</p>
                            <div
                                data-order-delivery-map
                                data-location-url="{{ route('orders.delivery-location', $order) }}"
                                data-status-target="#delivery-location-status-{{ $order->id }}"
                                role="application"
                                aria-label="Live delivery map for {{ $order->order_number }}"
                                class="h-64 w-full rounded-xl bg-[#F6F2E8] sm:h-80"
                            ></div>
                            <p class="mt-2 text-[11px] leading-5 text-[#68695f]">Detailed OpenStreetMap tiles require internet. This map shows live pin updates, not turn-by-turn navigation.</p>
                        </div>
                    @endif
                </section>
                <div class="flex flex-col justify-between gap-3 px-5 py-4 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-sm font-black text-[#2B2B2B]">Total · UGX {{ number_format($order->total) }}</p>
                        @if ($order->notes)<p class="mt-1 text-xs text-[#68695f]">Note: {{ $order->notes }}</p>@endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                    @if ($order->seller_id)
                        <a href="{{ route('messages.show', $order) }}" class="inline-flex min-h-12 items-center rounded-xl border border-[#DED5C1] px-4 text-xs font-extrabold text-[#315D3E] hover:bg-[#F6F2E8]">💬 Message {{ $sellerView ? 'buyer' : 'seller' }}</a>
                    @endif
                    @if ($sellerView && $order->status === 'pending')
                        <form method="POST" action="{{ route('seller.orders.update', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button class="min-h-12 rounded-xl bg-[#4A7C59] px-4 text-xs font-extrabold text-white hover:bg-[#3f6b4c]">Confirm order</button>
                        </form>
                    @elseif ($sellerView && $order->status === 'confirmed' && $order->delivery_status === 'not_scheduled')
                        <form method="POST" action="{{ route('seller.orders.delivery', $order) }}" class="flex flex-wrap items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="delivery_status" value="scheduled">
                            <label class="sr-only" for="eta-{{ $order->id }}">Estimated delivery time</label>
                            <input id="eta-{{ $order->id }}" name="estimated_delivery_at" type="datetime-local" min="{{ now()->format('Y-m-d\TH:i') }}" class="min-h-12 rounded-xl border-[#DED5C1] text-xs" aria-label="Estimated delivery time">
                            <button class="min-h-12 rounded-xl bg-[#4A7C59] px-4 text-xs font-extrabold text-white hover:bg-[#3f6b4c]">Schedule delivery</button>
                        </form>
                    @elseif ($sellerView && $order->delivery_status === 'scheduled')
                        <form method="POST" action="{{ route('seller.orders.delivery', $order) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="delivery_status" value="in_transit">
                            <button class="min-h-12 rounded-xl bg-[#4A7C59] px-4 text-xs font-extrabold text-white hover:bg-[#3f6b4c]">Mark in transit</button>
                        </form>
                    @elseif ($sellerView && $order->delivery_status === 'in_transit')
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('seller.orders.delivery-share', $order) }}">
                                @csrf
                                <button class="min-h-12 rounded-xl border border-[#DED5C1] px-4 text-xs font-extrabold text-[#315D3E] hover:bg-[#F6F2E8]">Create rider link</button>
                            </form>
                            @if ($order->deliveryShare?->isActive())
                                <form method="POST" action="{{ route('seller.orders.delivery-share.revoke', $order) }}" onsubmit="return confirm('Revoke the rider link and clear the last GPS point?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="min-h-12 rounded-xl border border-[#efcbbb] px-4 text-xs font-extrabold text-[#7B3A2A] hover:bg-[#fff1e8]">Revoke rider link</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('seller.orders.delivery', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="delivery_status" value="delivered">
                                <button class="min-h-12 rounded-xl bg-[#4A7C59] px-4 text-xs font-extrabold text-white hover:bg-[#3f6b4c]">Mark delivered</button>
                            </form>
                        </div>
                    @elseif (! $sellerView && $order->status === 'pending')
                        <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Cancel this order? Its stock will be returned to the seller.')">
                            @csrf
                            @method('PATCH')
                            <button class="min-h-12 rounded-xl border border-[#DED5C1] px-4 text-xs font-extrabold text-[#7B3A2A] hover:bg-[#fff1e8]">Cancel order</button>
                        </form>
                    @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-[14px] border border-dashed border-[#DED5C1] bg-white px-6 py-16 text-center">
                <h2 class="font-black text-[#2B2B2B]">{{ $sellerView ? 'No orders received yet' : 'No orders yet' }}</h2>
                <p class="mt-2 text-sm text-[#68695f]">{{ $sellerView ? 'New customer orders will appear here.' : 'When you place an order, its status and details will appear here.' }}</p>
                @unless ($sellerView)
                    <a href="{{ route('marketplace.index') }}" class="mt-4 inline-flex min-h-12 items-center text-sm font-extrabold text-[#315D3E] hover:underline">Browse marketplace →</a>
                @endunless
            </div>
        @endforelse
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
