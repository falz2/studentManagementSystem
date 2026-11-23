@extends('layouts.portal')

@section('page-title', 'Farm and supplier map')

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4A7C59]">Local poultry network</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-[#2B2B2B]">Farm and supplier map</h1>
            <p class="mt-2 text-sm text-[#68695f]">See opted-in sellers on a detailed online street map. Map tiles require an internet connection.</p>
        </div>
        <a href="{{ route('marketplace.index') }}" class="inline-flex min-h-12 items-center font-extrabold text-[#315D3E] hover:underline">Browse products →</a>
    </div>

    @if ($seller)
        <section class="mt-6 rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)] sm:p-6">
            <h2 class="text-lg font-black text-[#2B2B2B]">Your farm or shop location</h2>
            <p class="mt-1 text-sm text-[#68695f]">Use your device GPS to set a business pickup location. It is private unless you opt in to show it on the map.</p>
            <form method="POST" action="{{ route('seller.location.update') }}" data-seller-location-form class="mt-4">
                @csrf
                @method('PUT')
                <input type="hidden" name="latitude" value="{{ old('latitude', $seller->latitude) }}">
                <input type="hidden" name="longitude" value="{{ old('longitude', $seller->longitude) }}">
                <div class="flex flex-wrap gap-3">
                    <button type="button" data-capture-location class="min-h-12 rounded-xl border border-[#DED5C1] px-4 text-sm font-extrabold text-[#315D3E] hover:bg-[#F6F2E8]">📍 Capture this device's GPS</button>
                    <button type="submit" class="min-h-12 rounded-xl bg-[#4A7C59] px-5 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Save map settings</button>
                </div>
                <p class="mt-2 min-h-5 text-sm font-bold text-[#315D3E]" data-location-capture-status role="status" aria-live="polite"></p>
                @error('latitude')<p role="alert" class="mt-1 text-sm font-bold text-[#7B3A2A]">{{ $message }}</p>@enderror
                @error('longitude')<p role="alert" class="mt-1 text-sm font-bold text-[#7B3A2A]">{{ $message }}</p>@enderror
                <label class="mt-4 flex min-h-12 items-center gap-3 text-sm font-extrabold text-[#2B2B2B]">
                    <input type="checkbox" name="map_visible" value="1" @checked(old('map_visible', $seller->map_visible)) class="h-5 w-5 rounded border-[#DED5C1] text-[#4A7C59] focus:ring-[#4A7C59]">
                    Show my business location to signed-in PoultryHub users
                </label>
                <p class="mt-2 rounded-xl bg-[#FFF5D9] p-3 text-xs leading-5 text-[#624B1C]">
                    Only publish a farm/shop location that is safe for customers to see. Do not publish a private home location.
                    The interactive map uses OpenStreetMap tiles and needs an internet connection, but no API key.
                </p>
            </form>
        </section>
    @else
        <p class="mt-5 rounded-xl border border-[#f1ddb0] bg-[#FFF5D9] p-3 text-sm text-[#624B1C]">
            Farm locations appear only when a seller has explicitly opted in.
        </p>
    @endif

    <section class="mt-6 overflow-hidden rounded-[14px] border border-[#e8dfcf] bg-white shadow-[0_6px_20px_rgba(91,74,50,0.06)]">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#EEE8DB] px-5 py-4">
            <div>
                <h2 class="font-black text-[#2B2B2B]">Public seller locations</h2>
                <p class="mt-1 text-xs font-bold text-[#68695f]">{{ $locations->count() }} opted-in {{ \Illuminate\Support\Str::plural('location', $locations->count()) }}</p>
            </div>
            <span class="text-xs font-bold text-[#68695f]">Green · poultry farm &nbsp; Terracotta · feed supplier</span>
        </div>
        <div data-farm-map data-locations-id="farm-map-locations" role="application" aria-label="Interactive Leaflet street map of Uganda and seller locations" class="h-[55vh] min-h-[360px] w-full bg-[#EAF2E6]"></div>
        <p class="border-t border-[#EEE8DB] px-5 py-3 text-xs leading-5 text-[#68695f]">Street and place details are provided by OpenStreetMap. The map shows seller pins, not navigation directions.</p>
        @if ($locations->isEmpty())
            <p class="border-t border-[#EEE8DB] px-5 py-4 text-sm text-[#68695f]">No sellers have opted in yet. The map will show public farm and supplier pins here.</p>
        @endif
    </section>
    <script id="farm-map-locations" type="application/json">@json($locations)</script>
@endsection
