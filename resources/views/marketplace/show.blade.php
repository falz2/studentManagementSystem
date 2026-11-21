@extends('layouts.portal')

@section('page-title', $product->name)

@section('content')
    <a href="{{ route('marketplace.index') }}" class="inline-flex min-h-12 items-center text-sm font-extrabold text-[#4A7C59] hover:underline">← Back to marketplace</a>
    <div class="mt-3 grid overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white shadow-[0_8px_24px_rgba(91,74,50,0.07)] lg:grid-cols-2">
        @if ($product->image_path)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-72 w-full object-cover lg:h-full lg:min-h-[440px]">
        @else
            <img src="{{ $product->category === 'feed'
                ? 'https://images.unsplash.com/photo-1500595046743-cd271d694d30?auto=format&fit=crop&w=1200&q=72'
                : 'https://images.unsplash.com/photo-1548550023-2bdb3c5beed7?auto=format&fit=crop&w=1200&q=72' }}"
                alt="{{ $product->name }}"
                class="h-72 w-full object-cover lg:h-full lg:min-h-[440px]">
        @endif
        <div class="p-6 sm:p-9">
            <span class="rounded-full {{ $product->category === 'feed' ? 'bg-[#FFF3D7] text-[#805E14]' : 'bg-[#eef4e8] text-[#315D3E]' }} px-3 py-1.5 text-xs font-extrabold uppercase tracking-wide">{{ $product->category === 'feed' ? 'Poultry feed' : 'Poultry product' }}</span>
            <h1 class="mt-5 text-3xl font-black tracking-tight text-[#2B2B2B]">{{ $product->name }}</h1>
            <p class="mt-3 text-sm leading-6 text-[#575346]">{{ $product->description ?: 'Contact the seller for more information about this listing.' }}</p>
            <p class="mt-3 text-sm font-extrabold text-[#575346]">
                @if ($product->reviews_count > 0)
                    ⭐ {{ number_format((float) $product->reviews_avg_rating, 1) }} · {{ $product->reviews_count }} reviews
                @else
                    New listing · No reviews yet
                @endif
            </p>
            <p class="mt-4 text-2xl font-black text-[#2B2B2B]">UGX {{ number_format($product->price) }} <span class="text-sm font-bold text-[#68695f]">/ {{ $product->unit }}</span></p>
            @if ($product->quantity <= $product->low_stock_threshold)
                <p class="mt-2 inline-flex min-h-9 items-center rounded-full bg-[#FBEEE7] px-3 text-sm font-extrabold text-[#9A4E32]">⚠ Only {{ $product->quantity }} {{ \Illuminate\Support\Str::plural($product->unit, $product->quantity) }} left</p>
            @endif
            <dl class="mt-6 grid grid-cols-2 gap-4 border-y border-[#EEE8DB] py-5 text-sm">
                <div><dt class="text-xs font-bold text-[#68695f]">Seller</dt><dd class="mt-1 font-extrabold text-[#2B2B2B]">{{ $product->seller->name }}</dd></div>
                <div><dt class="text-xs font-bold text-[#68695f]">Available</dt><dd class="mt-1 font-extrabold text-[#2B2B2B]">{{ $product->quantity }} {{ \Illuminate\Support\Str::plural($product->unit, $product->quantity) }}</dd></div>
            </dl>
            @if ($product->quantity > 0)
                <form method="POST" action="{{ route('cart.add', $product) }}" class="mt-6 flex flex-col gap-3 sm:flex-row">
                    @csrf
                    <label class="sr-only" for="quantity">Quantity</label>
                    <input id="quantity" name="quantity" type="number" value="1" min="1" max="{{ $product->quantity }}" required class="min-h-12 w-full rounded-xl border-[#DED5C1] text-sm sm:w-28">
                    <button class="min-h-12 flex-1 rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Add to cart</button>
                </form>
                @error('quantity')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @else
                <p class="mt-6 rounded-xl bg-[#F6F1E5] p-3 text-sm font-extrabold text-[#68695f]">Currently out of stock</p>
            @endif
            @if ($role === 'consumer' && $product->seller->isSupplier() && $product->quantity > 0)
                <form method="POST" action="{{ route('recurring-orders.store') }}" class="mt-6 rounded-[14px] border border-[#e8dfcf] bg-[#FAF7EF] p-4">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <h2 class="font-black">Order this feed regularly</h2>
                    <p class="mt-1 text-xs leading-5 text-[#68695f]">A pending order will be created on the scheduled date. You can pause any time. No automatic payment.</p>
                    <div class="mt-3 grid gap-2 sm:grid-cols-3">
                        <label class="text-xs font-extrabold">Quantity
                            <input name="quantity" type="number" min="1" max="10000" value="{{ old('quantity', 1) }}" required class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm">
                        </label>
                        <label class="text-xs font-extrabold">Repeat
                            <select name="frequency" class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm">
                                <option value="weekly">Weekly</option>
                                <option value="monthly" selected>Monthly</option>
                            </select>
                        </label>
                        <label class="text-xs font-extrabold">First order date
                            <input name="next_run_at" type="date" min="{{ now()->addDay()->toDateString() }}" value="{{ now()->addWeek()->toDateString() }}" required class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm">
                        </label>
                    </div>
                    @error('product_id')<p class="mt-2 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    @error('quantity')<p class="mt-2 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    @error('next_run_at')<p class="mt-2 text-xs font-bold text-[#C0392B]">{{ $message }}</p>@enderror
                    <button class="mt-3 min-h-12 rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Save recurring order</button>
                </form>
            @endif
        </div>
    </div>

    <section class="mt-6 grid gap-5 lg:grid-cols-2">
        <div class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_5px_18px_rgba(74,65,41,0.05)]">
            <h2 class="text-lg font-black">Reviews from buyers</h2>
            <p class="mt-1 text-sm text-[#68695f]">Reviews can be added after a completed order.</p>
            <div class="mt-4 space-y-3">
                @forelse ($product->reviews as $review)
                    <article class="border-t border-[#EEE8DB] pt-3">
                        <div class="flex items-center justify-between gap-3"><strong>{{ $review->buyer->name }}</strong><span class="text-sm font-extrabold">⭐ {{ $review->rating }}/5</span></div>
                        @if ($review->body)<p class="mt-1 text-sm leading-6 text-[#575346]">{{ $review->body }}</p>@endif
                        <time class="mt-1 block text-xs font-bold text-[#777263]">{{ $review->created_at->format('M j, Y') }}</time>
                    </article>
                @empty
                    <p class="border-t border-[#EEE8DB] pt-3 text-sm text-[#68695f]">No reviews yet. Buyers can share feedback after a completed purchase.</p>
                @endforelse
            </div>
        </div>
        @if ($reviewableItems->isNotEmpty())
            <div class="rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_5px_18px_rgba(74,65,41,0.05)]">
                <h2 class="text-lg font-black">Review a purchase</h2>
                <p class="mt-1 text-sm text-[#68695f]">Only completed orders can be reviewed.</p>
                @foreach ($reviewableItems as $item)
                    <form method="POST" action="{{ route('marketplace.reviews.store', $product) }}" class="mt-4 border-t border-[#EEE8DB] pt-4">
                        @csrf
                        <input type="hidden" name="order_item_id" value="{{ $item->id }}">
                        <p class="text-sm font-extrabold">{{ $item->order->order_number }} · {{ $item->quantity }} {{ \Illuminate\Support\Str::plural($item->unit, $item->quantity) }}</p>
                        <label for="rating-{{ $item->id }}" class="mt-2 block text-xs font-extrabold">Your rating</label>
                        <select id="rating-{{ $item->id }}" name="rating" required class="mt-1 min-h-12 w-full rounded-xl border-[#DED5C1] text-sm sm:max-w-48">
                            <option value="">Choose 1–5 stars</option>
                            @for ($rating = 5; $rating >= 1; $rating--)
                                <option value="{{ $rating }}">{{ $rating }} {{ \Illuminate\Support\Str::plural('star', $rating) }}</option>
                            @endfor
                        </select>
                        <label for="review-body-{{ $item->id }}" class="mt-2 block text-xs font-extrabold">Comment <span class="font-normal">(optional)</span></label>
                        <textarea id="review-body-{{ $item->id }}" name="body" rows="3" maxlength="2000" class="mt-1 w-full rounded-xl border-[#DED5C1] text-sm"></textarea>
                        <button class="mt-3 min-h-12 rounded-xl bg-[#4A7C59] px-4 text-sm font-extrabold text-white">Submit review</button>
                    </form>
                @endforeach
            </div>
        @endif
    </section>
@endsection
