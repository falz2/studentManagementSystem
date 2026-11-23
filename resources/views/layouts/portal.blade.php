<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#FDF6E3">
    <title>@yield('page-title', 'PoultryHub') · PoultryHub</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/poultryhub-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#FDF6E3] font-sans text-[#2B2B2B] antialiased">
    @php($cartCount = array_sum(session('cart', [])))
    @php($unreadCount = auth()->user()->unreadNotifications()->count())
    <header class="sticky top-0 z-30 border-b border-[#e8dfcf] bg-[#fffdf7]/95 shadow-[0_3px_18px_rgba(91,74,50,0.05)] backdrop-blur">
        <div class="flex h-[4.5rem] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button type="button" data-sidebar-toggle aria-expanded="false" aria-controls="portal-sidebar" class="flex h-12 w-12 items-center justify-center rounded-xl border border-[#DED5C1] bg-white text-[#315D3E] lg:hidden" aria-label="Toggle navigation">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ route('marketplace.index') }}" class="flex min-w-0 items-center gap-3">
                    <img src="{{ asset('images/poultryhub-logo.jpg') }}" alt="" class="h-10 w-9 shrink-0 rounded-lg object-contain">
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-extrabold leading-tight text-[#34362f]">PoultryHub</span>
                        <span class="hidden text-[11px] text-slate-500 sm:block">A better poultry marketplace</span>
                    </span>
                </a>
            </div>
            <div class="flex items-center gap-1 sm:gap-2">
                <a href="{{ route('cart.index') }}" class="relative flex min-h-12 items-center rounded-xl px-3 text-sm font-extrabold text-[#575346] hover:bg-[#F6F1E5] hover:text-[#315D3E]" aria-label="Shopping cart, {{ $cartCount }} items">
                    Cart
                    @if ($cartCount > 0)
                        <span class="ml-1 flex h-6 min-w-6 items-center justify-center rounded-full bg-[#4A7C59] px-1 text-xs font-black text-white">{{ $cartCount }}</span>
                    @endif
                </a>
                <a href="{{ route('messages.index') }}" class="hidden min-h-12 items-center rounded-xl px-3 text-sm font-extrabold text-[#575346] hover:bg-[#F6F1E5] sm:flex">Messages</a>
                <a href="{{ route('notifications.index') }}" class="relative flex min-h-12 items-center rounded-xl px-3 text-sm font-extrabold text-[#575346] hover:bg-[#F6F1E5]" aria-label="Notifications, {{ $unreadCount }} unread">Notifications
                    @if ($unreadCount > 0)<span class="ml-1 flex h-6 min-w-6 items-center justify-center rounded-full bg-[#4A7C59] px-1 text-xs font-black text-white">{{ $unreadCount }}</span>@endif
                </a>
                <div class="hidden text-right sm:block">
                    <span class="block max-w-40 truncate text-xs font-extrabold text-[#2B2B2B]">{{ auth()->user()->name }}</span>
                    <span class="block text-[10px] capitalize text-[#68695f]">{{ str($role)->replace('_', ' ') }}</span>
                </div>
                <a href="{{ route('profile.show') }}" class="flex h-11 w-11 items-center justify-center rounded-full bg-[#EAF2E6] text-xs font-black text-[#315D3E]" title="{{ auth()->user()->name }}" aria-label="Profile settings for {{ auth()->user()->name }}">{{ str(auth()->user()->name)->substr(0, 2)->upper() }}</a>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="min-h-12 rounded-xl px-3 text-xs font-extrabold text-[#68695f] transition hover:bg-[#F6F1E5] hover:text-[#315D3E]">Log out</button>
                </form>
            </div>
        </div>
    </header>

    <div class="min-h-[calc(100vh-4.5rem)] lg:flex">
        <aside id="portal-sidebar" class="hidden border-b border-[#e8dfcf] bg-[#fffdf7] p-4 lg:block lg:min-h-[calc(100vh-4.5rem)] lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:px-5 lg:py-8" aria-label="Sidebar navigation">
            @if (in_array($role, ['farmer', 'supplier'], true))
                <p class="mb-3 px-3 text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#777263]">Seller workspace</p>
                <nav class="flex flex-col gap-1" aria-label="Main navigation">
                    <a href="{{ route('seller.dashboard') }}" class="mobile-nav-link {{ request()->routeIs('seller.dashboard') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">🏠 Overview</a>
                    <a href="{{ route('seller.products.index') }}" class="mobile-nav-link {{ request()->routeIs('seller.products.*') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">🥚 My products</a>
                    <a href="{{ route('seller.products.create') }}" class="mobile-nav-link">＋ Add a product</a>
                    <a href="{{ route('seller.orders') }}" class="mobile-nav-link {{ request()->routeIs('seller.orders*') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">📦 Orders received</a>
                    <a href="{{ route('marketplace.map') }}" class="mobile-nav-link {{ request()->routeIs('marketplace.map') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">🗺️ Farm map</a>
                    <a href="{{ route('seller.analytics') }}" class="mobile-nav-link {{ request()->routeIs('seller.analytics') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">📊 Analytics</a>
                    <div class="my-3 border-t border-slate-100"></div>
                    <a href="{{ route('marketplace.index') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Browse marketplace</a>
                    <a href="{{ route('orders.index') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">My purchases</a>
                </nav>
            @elseif ($role === 'admin')
                <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Administration</p>
                <nav class="flex flex-col gap-1" aria-label="Main navigation">
                    <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link {{ request()->routeIs('admin.dashboard') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">📊 Platform overview</a>
                    <a href="{{ route('marketplace.index') }}" class="mobile-nav-link">🛒 Marketplace</a>
                    <a href="{{ route('marketplace.map') }}" class="mobile-nav-link">🗺️ Farm map</a>
                </nav>
            @else
                <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">Marketplace</p>
                <nav class="flex flex-col gap-1" aria-label="Main navigation">
                    <a href="{{ route('marketplace.index') }}" class="mobile-nav-link {{ request()->routeIs('marketplace.index', 'marketplace.show') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">🛒 Browse products</a>
                    <a href="{{ route('marketplace.map') }}" class="mobile-nav-link {{ request()->routeIs('marketplace.map') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">🗺️ Farm map</a>
                    <a href="{{ route('orders.index') }}" class="mobile-nav-link {{ request()->routeIs('orders.*') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">📦 My orders</a>
                    <a href="{{ route('recurring-orders.index') }}" class="mobile-nav-link {{ request()->routeIs('recurring-orders.*') ? 'bg-[#EAF2E6] text-[#315D3E]' : '' }}">🔁 Recurring feed orders</a>
                </nav>
            @endif
            <div class="mt-4 flex flex-col gap-1 border-t border-[#EEE8DB] pt-3">
                <a href="{{ route('profile.show') }}" class="mobile-nav-link">👤 Profile settings</a>
                <a href="{{ route('addresses.index') }}" class="mobile-nav-link">📍 Delivery addresses</a>
                <a href="{{ route('messages.index') }}" class="mobile-nav-link">💬 Messages</a>
                <a href="{{ route('notifications.index') }}" class="mobile-nav-link">🔔 Notifications @if($unreadCount > 0)<span class="rounded-full bg-[#4A7C59] px-2 py-0.5 text-[10px] text-white">{{ $unreadCount }}</span>@endif</a>
            </div>
        </aside>

        <main class="flex min-w-0 flex-1 flex-col px-4 pb-28 pt-5 sm:px-6 sm:pt-7 lg:px-8 lg:pb-8">
            <div class="flex-1">
                @if (session('success'))
                    <div role="status" class="mb-5 rounded-[14px] border border-[#cfe0cf] bg-[#eef4e8] px-4 py-3 text-sm font-bold text-[#315D3E]">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div role="alert" class="mb-5 rounded-[14px] border border-[#efcbbb] bg-[#fff1e8] px-4 py-3 text-sm font-bold text-[#7B3A2A]">{{ session('error') }}</div>
                @endif
                @error('cart')
                    <div role="alert" class="mb-5 rounded-[14px] border border-[#efcbbb] bg-[#fff1e8] px-4 py-3 text-sm font-bold text-[#7B3A2A]">{{ $message }}</div>
                @enderror
                @yield('content')
            </div>
            <footer class="mt-12 border-t border-[#e8dfcf] pt-5 text-xs text-[#68695f]">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <span>© {{ now()->format('Y') }} PoultryHub</span>
                    <span>Connecting poultry farmers, feed suppliers and consumers across Uganda</span>
                </div>

            </footer>
        </main>
    </div>

    <nav class="fixed inset-x-0 bottom-0 z-30 border-t border-[#e8dfcf] bg-[#FFFCF4]/95 px-2 pb-[max(env(safe-area-inset-bottom),0.5rem)] pt-2 shadow-[0_-5px_20px_rgba(74,65,41,0.07)] backdrop-blur lg:hidden" aria-label="Quick navigation">
        <div class="mx-auto grid max-w-2xl grid-cols-6 gap-1">
            @if (in_array($role, ['farmer', 'supplier'], true))
                <a href="{{ route('seller.dashboard') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">⌂</span>Home</a>
                <a href="{{ route('seller.products.index') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">▦</span>Products</a>
                <a href="{{ route('marketplace.map') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">🗺️</span>Map</a>
            @else
                <a href="{{ route('marketplace.index') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">⌂</span>Browse</a>
                <a href="{{ route('orders.index') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">▣</span>Orders</a>
                <a href="{{ route('marketplace.map') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">🗺️</span>Map</a>
            @endif
            <a href="{{ route('cart.index') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">🛒</span>Cart · {{ $cartCount }}</a>
            <a href="{{ route('messages.index') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">💬</span>Messages</a>
            <a href="{{ route('notifications.index') }}" class="flex min-h-12 flex-col items-center justify-center rounded-xl text-[10px] font-extrabold text-[#575346]"><span aria-hidden="true">🔔</span>Updates · {{ $unreadCount }}</a>
        </div>
    </nav>
    <script>
        const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
        const sidebar = document.getElementById('portal-sidebar');

        sidebarToggle?.addEventListener('click', () => {
            const isExpanded = sidebarToggle.getAttribute('aria-expanded') === 'true';
            sidebarToggle.setAttribute('aria-expanded', String(!isExpanded));
            sidebar?.classList.toggle('hidden', isExpanded);
        });
    </script>
</body>
</html>
