@extends('layouts.portal')

@section('page-title', 'Lecturer dashboard')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#d97706]">Faculty workspace · Fall 2026</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Welcome back, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-sm text-slate-500">Your teaching schedule and student activity at a glance.</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-500"><span class="font-semibold text-slate-900">Today</span> · {{ now()->format('l, F j') }}</div>
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-3">
        @foreach ([['Assigned courses', '3', 'This semester', '▤', 'bg-blue-50', 'text-[#2563eb]'], ['Registered students', '86', 'Across your courses', '♙', 'bg-amber-50', 'text-[#d97706]'], ['Awaiting review', '8', 'New registrations', '◎', 'bg-blue-50', 'text-[#2563eb]']] as [$label, $value, $detail, $icon, $iconBg, $iconColor])
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">{{ $label }}</span><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $iconBg }} text-lg {{ $iconColor }}">{{ $icon }}</span></div>
                <p class="mt-5 text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</p>
                <p class="mt-1 text-xs text-slate-400">{{ $detail }}</p>
            </article>
        @endforeach
    </section>

    <section id="my-courses" class="mt-8">
        <div class="mb-4 flex items-end justify-between">
            <div><h2 class="text-lg font-bold text-slate-900">Your courses</h2><p class="mt-1 text-xs text-slate-500">Courses assigned to you this semester</p></div>
            <span class="text-xs font-medium text-slate-400">3 courses</span>
        </div>
        <div class="grid gap-4 lg:grid-cols-3">
            @foreach ([['CS101', 'Foundations of Computing', 'Monday · 09:00–10:30', '32', '40', 'bg-blue-50', 'text-[#2563eb]'], ['CS220', 'Database Systems', 'Wednesday · 13:00–14:30', '24', '35', 'bg-amber-50', 'text-[#d97706]'], ['CS340', 'Software Architecture', 'Friday · 10:00–11:30', '30', '32', 'bg-blue-50', 'text-[#2563eb]']] as [$code, $name, $time, $students, $capacity, $colorBg, $colorText])
                <article class="rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-start justify-between"><span class="rounded-lg {{ $colorBg }} px-2.5 py-1.5 text-[10px] font-bold tracking-wide {{ $colorText }}">{{ $code }}</span><span class="text-xs text-slate-400">Fall 2026</span></div>
                    <h3 class="mt-5 text-base font-bold text-slate-900">{{ $name }}</h3>
                    <p class="mt-1 text-xs text-slate-500">{{ $time }} · Science Hall</p>
                    <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                        <span class="text-xs text-slate-500"><strong class="text-slate-700">{{ $students }}</strong> / {{ $capacity }} students</span>
                        <a href="#students" class="text-xs font-semibold text-[#2563eb] hover:underline">View roster →</a>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section id="students" class="mt-8 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 sm:px-6">
            <div><h2 class="text-base font-bold text-slate-900">Recently registered students</h2><p class="mt-1 text-xs text-slate-500">Students enrolled in your courses</p></div>
            <a href="#my-courses" class="text-xs font-semibold text-[#2563eb]">Your courses →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[560px] text-left">
                <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500"><tr><th class="px-6 py-3">Student</th><th class="px-4 py-3">Course</th><th class="px-4 py-3">Student ID</th><th class="px-6 py-3">Registered</th></tr></thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @foreach ([['Maya Chen', 'MC', 'Database Systems', 'STU-2024-0182', 'Sep 02, 2026'], ['Sofia Patel', 'SP', 'Foundations of Computing', 'STU-2025-0028', 'Sep 04, 2026'], ['Noah Williams', 'NW', 'Software Architecture', 'STU-2023-0153', 'Sep 05, 2026'], ['Amara Okafor', 'AO', 'Database Systems', 'STU-2024-0217', 'Sep 06, 2026']] as [$name, $initials, $course, $id, $date])
                        <tr class="hover:bg-slate-50">
                            <td class="px-6 py-4"><span class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-[10px] font-bold text-[#2563eb]">{{ $initials }}</span><span class="font-semibold text-slate-700">{{ $name }}</span></span></td>
                            <td class="px-4 py-4 font-medium text-slate-600">{{ $course }}</td><td class="px-4 py-4 text-slate-500">{{ $id }}</td><td class="px-6 py-4 text-slate-500">{{ $date }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endsection
