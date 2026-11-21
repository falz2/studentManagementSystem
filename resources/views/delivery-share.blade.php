<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FDF6E3">
    <meta name="referrer" content="no-referrer">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Delivery location sharing · PoultryHub</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/poultryhub-logo.jpg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FDF6E3] font-sans text-[#2B2B2B] antialiased">
    <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6">
        <a href="{{ route('marketplace.index') }}" class="inline-flex min-h-12 items-center gap-3 font-extrabold text-[#315D3E]">
            <img src="{{ asset('images/poultryhub-logo.jpg') }}" alt="" class="h-10 w-9 rounded-lg object-contain">
            PoultryHub
        </a>
        <section class="mt-5 rounded-[14px] border border-[#e8dfcf] bg-white p-5 shadow-[0_6px_20px_rgba(91,74,50,0.06)] sm:p-7">
            <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#4A7C59]">Private delivery link</p>
            <h1 class="mt-2 text-2xl font-black text-[#2B2B2B]">Share delivery location</h1>
            <p class="mt-2 text-sm leading-6 text-[#68695f]">
                Tap Start and allow GPS access while delivering. The link holder and the buyer and seller for this order can see the shared pin.
                Sharing stops when delivery ends or this link expires.
            </p>
            <div class="mt-4 flex flex-wrap gap-3">
                <button type="button" data-start-location-sharing class="min-h-12 rounded-xl bg-[#4A7C59] px-5 text-sm font-extrabold text-white hover:bg-[#3f6b4c]">Start sharing location</button>
                <button type="button" data-stop-location-sharing hidden class="min-h-12 rounded-xl border border-[#DED5C1] px-5 text-sm font-extrabold text-[#315D3E] hover:bg-[#F6F2E8]">Stop sharing on this device</button>
            </div>
            <p data-rider-share-status role="status" aria-live="polite" class="mt-3 min-h-6 text-sm font-bold text-[#315D3E]">
                GPS sharing is off. Start sharing only when you are ready to deliver.
            </p>
            <div data-rider-share-map data-location-url="{{ route('delivery-share.location') }}" role="application" aria-label="Map showing the delivery device location" class="mt-4 h-[55vh] min-h-[340px] w-full rounded-xl bg-[#F6F2E8]"></div>
            <p class="mt-3 rounded-xl bg-[#FFF5D9] p-3 text-xs leading-5 text-[#624B1C]">
                Your GPS point is sent to PoultryHub and can be seen by anyone holding this link and by the order's buyer and seller. Detailed OpenStreetMap tiles require an internet connection but no API key. This link is private; do not forward it to anyone else.
            </p>
            <noscript><p class="mt-3 text-sm font-bold text-[#7B3A2A]">Enable JavaScript to use the live map and GPS sharing.</p></noscript>
        </section>
    </main>
</body>
</html>
