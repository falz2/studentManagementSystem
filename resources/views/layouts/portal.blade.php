<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <title>@yield('page-title', 'Dashboard') · Northstar University</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white font-sans text-slate-900 antialiased">
    <header class="fixed inset-x-0 top-0 z-30 border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-[#2563eb]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5 12 4l9 5.5-9 5.5-9-5.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 12v4.5c3.4 2.7 7.6 2.7 11 0V12M21 10v6"/></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold leading-tight text-slate-900">Northstar University</span>
                        <span class="block text-[11px] text-slate-500">Student management system</span>
                    </span>
                </a>

                <nav class="hidden items-center gap-7 md:flex" aria-label="Main navigation">
                    @if ($role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'text-slate-900' : 'text-slate-500 hover:text-slate-900' }} text-sm font-medium transition-colors">Overview</a>
                        @foreach (['students' => 'Students', 'courses' => 'Courses', 'lecturers' => 'Lecturers', 'registrations' => 'Registrations'] as $key => $label)
                            <a href="{{ route('admin.'.$key) }}" class="{{ request()->routeIs('admin.'.$key) ? 'text-[#2563eb]' : 'text-slate-500 hover:text-slate-900' }} text-sm font-medium transition-colors">{{ $label }}</a>
                        @endforeach
                    @elseif ($role === 'lecturer')
                        <a href="{{ route('lecturer.dashboard') }}" class="text-sm font-medium text-slate-900">Overview</a>
                        <a href="#my-courses" class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">My courses</a>
                        <a href="#students" class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">Students</a>
                    @else
                        <a href="{{ route('student.dashboard') }}" class="text-sm font-medium text-slate-900">My dashboard</a>
                        <a href="#my-registrations" class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">My registrations</a>
                        <a href="#available-courses" class="text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">Browse courses</a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-[#2563eb]" title="{{ auth()->user()->name }}">{{ str(auth()->user()->name)->substr(0, 2)->upper() }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-slate-500 hover:text-[#2563eb]">Log out</button>
                    </form>
                </nav>

                <button type="button" data-mobile-menu-toggle aria-expanded="false" aria-controls="mobile-navigation" class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 md:hidden" aria-label="Toggle navigation">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
            <nav id="mobile-navigation" class="hidden border-t border-slate-200 py-4 md:hidden" aria-label="Mobile navigation">
                <div class="flex flex-col gap-1">
                    @if ($role === 'admin')
                        <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link">Overview</a>
                        @foreach (['students' => 'Students', 'courses' => 'Courses', 'lecturers' => 'Lecturers', 'registrations' => 'Registrations'] as $key => $label)
                            <a href="{{ route('admin.'.$key) }}" class="mobile-nav-link">{{ $label }}</a>
                        @endforeach
                    @elseif ($role === 'lecturer')
                        <a href="{{ route('lecturer.dashboard') }}" class="mobile-nav-link">Overview</a>
                        <a href="#my-courses" class="mobile-nav-link">My courses</a>
                        <a href="#students" class="mobile-nav-link">Students</a>
                    @else
                        <a href="{{ route('student.dashboard') }}" class="mobile-nav-link">My dashboard</a>
                        <a href="#my-registrations" class="mobile-nav-link">My registrations</a>
                        <a href="#available-courses" class="mobile-nav-link">Browse courses</a>
                    @endif
                    <a href="{{ route('profile.show') }}" class="mobile-nav-link">Profile settings</a>
                    <form method="POST" action="{{ route('logout') }}" class="px-3 pt-2">
                        @csrf
                        <button type="submit" class="text-sm font-medium text-slate-500 hover:text-[#2563eb]">Log out</button>
                    </form>
                </div>
            </nav>
        </div>
    </header>

    <main class="mx-auto min-h-screen max-w-7xl px-4 pb-8 pt-28 sm:px-6 lg:px-8">
        @yield('content')
        <footer class="mt-12 flex flex-col gap-2 border-t border-slate-200 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ now()->format('Y') }} Northstar University</span>
            <span>Student management system <span class="px-1 text-[#d97706]">·</span> Academic year 2026–2027</span>
        </footer>
    </main>

    <script>
        const mobileMenuToggle = document.querySelector('[data-mobile-menu-toggle]');
        const mobileNavigation = document.getElementById('mobile-navigation');

        mobileMenuToggle?.addEventListener('click', () => {
            const isExpanded = mobileMenuToggle.getAttribute('aria-expanded') === 'true';
            mobileMenuToggle.setAttribute('aria-expanded', String(!isExpanded));
            mobileNavigation?.classList.toggle('hidden', isExpanded);
        });
    </script>
</body>
</html>
