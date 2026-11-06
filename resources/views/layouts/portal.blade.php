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
    <header class="relative z-30 border-b border-slate-200 bg-white">
        <div class="flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <button type="button" data-sidebar-toggle aria-expanded="false" aria-controls="portal-sidebar" class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 lg:hidden" aria-label="Toggle navigation">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <a href="{{ $role === 'admin' ? route('admin.dashboard') : ($role === 'lecturer' ? route('lecturer.dashboard') : route('student.dashboard')) }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-blue-50 text-[#2563eb]">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5 12 4l9 5.5-9 5.5-9-5.5Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 12v4.5c3.4 2.7 7.6 2.7 11 0V12M21 10v6"/></svg>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold leading-tight text-slate-900">Northstar University</span>
                        <span class="block text-[11px] text-slate-500">Student management system</span>
                    </span>
                </a>
            </div>
            <div class="flex items-center gap-4">
                <a href="{{ route('profile.show') }}" class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-50 text-xs font-bold text-[#2563eb]" title="{{ auth()->user()->name }}">{{ str(auth()->user()->name)->substr(0, 2)->upper() }}</a>
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button type="submit" class="text-sm font-medium text-slate-500 hover:text-[#2563eb]">Log out</button>
                </form>
            </div>
        </div>
    </header>

    <div class="min-h-[calc(100vh-5rem)] lg:flex">
        <aside id="portal-sidebar" class="hidden border-b border-slate-200 bg-slate-50 p-4 lg:block lg:min-h-[calc(100vh-5rem)] lg:w-64 lg:shrink-0 lg:border-b-0 lg:border-r lg:px-5 lg:py-8" aria-label="Sidebar navigation">
            <p class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[0.16em] text-slate-400">{{ $role === 'admin' ? 'Administration' : ($role === 'lecturer' ? 'Faculty workspace' : 'Student workspace') }}</p>
            <nav class="flex flex-col gap-1" aria-label="Main navigation">
                @if ($role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}" class="rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-50 text-[#2563eb]' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">Overview</a>
                    @foreach (['students' => 'Students', 'courses' => 'Courses', 'lecturers' => 'Lecturers', 'registrations' => 'Registrations'] as $key => $label)
                        <a href="{{ route('admin.'.$key) }}" aria-current="{{ request()->routeIs('admin.'.$key) ? 'page' : 'false' }}" class="rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('admin.'.$key) ? 'bg-blue-50 text-[#2563eb]' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">{{ $label }}</a>
                    @endforeach
                @elseif ($role === 'lecturer')
                    <a href="{{ route('lecturer.dashboard') }}" aria-current="{{ request()->routeIs('lecturer.dashboard') ? 'page' : 'false' }}" class="rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('lecturer.dashboard') ? 'bg-blue-50 text-[#2563eb]' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">Overview</a>
                    <a href="{{ route('lecturer.dashboard') }}#my-courses" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-white hover:text-slate-900">My courses</a>
                    <a href="{{ route('lecturer.dashboard') }}#students" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-white hover:text-slate-900">Students</a>
                @else
                    <a href="{{ route('student.dashboard') }}" aria-current="{{ request()->routeIs('student.dashboard') ? 'page' : 'false' }}" class="rounded-lg px-3 py-2.5 text-sm font-medium transition-colors {{ request()->routeIs('student.dashboard') ? 'bg-blue-50 text-[#2563eb]' : 'text-slate-600 hover:bg-white hover:text-slate-900' }}">My dashboard</a>
                    <a href="{{ route('student.dashboard') }}#my-registrations" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-white hover:text-slate-900">My registrations</a>
                    <a href="{{ route('student.dashboard') }}#available-courses" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-white hover:text-slate-900">Browse courses</a>
                @endif
                <a href="{{ route('profile.show') }}" class="rounded-lg px-3 py-2.5 text-sm font-medium text-slate-600 transition-colors hover:bg-white hover:text-slate-900">Profile settings</a>
            </nav>
        </aside>

        <main class="min-w-0 flex-1 px-4 pb-8 pt-8 sm:px-6 lg:px-8">
            @yield('content')
            <footer class="mt-12 flex flex-col gap-2 border-t border-slate-200 pt-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ now()->format('Y') }} African Union University</span>
                <span>Student management system <span class="px-1 text-[#d97706]">·</span> Academic year 2026–2027</span>
            </footer>
        </main>
    </div>

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
