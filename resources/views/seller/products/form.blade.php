@extends('layouts.portal')

@section('page-title', $title)

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('seller.products.index') }}" class="inline-flex min-h-12 items-center text-sm font-extrabold text-[#4A7C59] hover:underline">← Back to my products</a>
        <h1 class="mt-3 text-3xl font-black tracking-tight text-[#2B2B2B]">{{ $title }}</h1>
        <p class="mt-2 text-sm text-[#68695f]">Set a clear price, track low stock, and add an expiry date for products that need one.</p>
        <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-7 space-y-5 rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)] sm:p-8">
            @csrf
            @if ($method !== 'POST') @method($method) @endif
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="category" class="text-sm font-medium text-slate-700">Category</label>
                    <select id="category" name="category" required class="mt-2 block w-full rounded-lg border-slate-200 text-sm focus:border-[#3d7b3c] focus:ring-[#3d7b3c]">
                        @foreach ($categories as $value => $label)
                            <option value="{{ $value }}" @selected(old('category', $product->category ?: array_key_first($categories)) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="name" class="text-sm font-medium text-slate-700">Product name</label>
                    <input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="255" placeholder="e.g. Free-range chicken" class="mt-2 block w-full rounded-lg border-slate-200 text-sm focus:border-[#3d7b3c] focus:ring-[#3d7b3c]">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="price" class="text-sm font-medium text-slate-700">Price (UGX)</label>
                    <input id="price" name="price" type="number" value="{{ old('price', $product->price) }}" min="1" max="1000000000" required class="mt-2 block w-full rounded-lg border-slate-200 text-sm focus:border-[#3d7b3c] focus:ring-[#3d7b3c]">
                    @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="quantity" class="text-sm font-medium text-slate-700">Available quantity</label>
                    <input id="quantity" name="quantity" type="number" value="{{ old('quantity', $product->quantity) }}" min="1" max="1000000" required class="mt-2 block w-full rounded-lg border-slate-200 text-sm focus:border-[#3d7b3c] focus:ring-[#3d7b3c]">
                    @error('quantity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="low_stock_threshold" class="text-sm font-medium text-slate-700">Low-stock alert at</label>
                    <input id="low_stock_threshold" name="low_stock_threshold" type="number" value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 5) }}" min="0" max="1000000" class="mt-2 block min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                    <p class="mt-1 text-xs text-[#68695f]">Get an alert when stock reaches this amount.</p>
                    @error('low_stock_threshold')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="expires_at" class="text-sm font-medium text-slate-700">Expiry date <span class="font-normal text-slate-400">(optional)</span></label>
                    <input id="expires_at" name="expires_at" type="date" value="{{ old('expires_at', $product->expires_at?->toDateString()) }}" min="{{ today()->toDateString() }}" class="mt-2 block min-h-12 w-full rounded-xl border-[#DED5C1] text-sm focus:border-[#4A7C59] focus:ring-[#4A7C59]">
                    <p class="mt-1 text-xs text-[#68695f]">Expired listings are hidden; alerts are sent up to 30 days ahead.</p>
                    @error('expires_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="unit" class="text-sm font-medium text-slate-700">Unit</label>
                    <select id="unit" name="unit" required class="mt-2 block w-full rounded-lg border-slate-200 text-sm focus:border-[#3d7b3c] focus:ring-[#3d7b3c]">
                        @foreach (['bird' => 'Bird', 'kg' => 'Kilogram (kg)', 'tray' => 'Tray', 'bag' => 'Bag'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('unit', $product->unit ?: 'bird') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('unit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status" class="text-sm font-medium text-slate-700">Listing visibility</label>
                    <select id="status" name="status" required class="mt-2 block w-full rounded-lg border-slate-200 text-sm focus:border-[#3d7b3c] focus:ring-[#3d7b3c]">
                        <option value="active" @selected(old('status', $product->status ?: 'active') === 'active')>Visible on marketplace</option>
                        <option value="inactive" @selected(old('status', $product->status) === 'inactive')>Hidden</option>
                    </select>
                    @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="description" class="text-sm font-medium text-slate-700">Description <span class="font-normal text-slate-400">(optional)</span></label>
                <textarea id="description" name="description" rows="4" maxlength="2000" placeholder="Share useful details about your product" class="mt-2 block w-full rounded-lg border-slate-200 text-sm focus:border-[#3d7b3c] focus:ring-[#3d7b3c]">{{ old('description', $product->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="image" class="text-sm font-medium text-slate-700">Product photo <span class="font-normal text-slate-400">(optional, max 2 MB)</span></label>
                <input id="image" name="image" type="file" accept="image/*" class="mt-2 block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#edf6e9] file:px-3 file:py-2 file:text-xs file:font-semibold file:text-[#3d7b3c]">
                @if ($product->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="Current photo of {{ $product->name }}" class="mt-3 h-24 w-24 rounded-lg object-cover">@endif
                @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('seller.products.index') }}" class="rounded-lg border border-slate-200 px-4 py-3 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                <button class="min-h-12 rounded-xl bg-[#4A7C59] px-5 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">{{ $method === 'POST' ? 'Publish listing' : 'Save changes' }}</button>
            </div>
        </form>
    </div>
@endsection
