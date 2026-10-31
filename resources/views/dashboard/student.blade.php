@extends('layouts.portal')

@section('page-title', 'Student dashboard')

@section('content')
    <section class="relative overflow-hidden rounded-2xl bg-[#1e3a8a] px-6 py-8 text-white sm:px-9 sm:py-10">
        <div class="relative z-10 max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#fbbf24]">{{ now()->format('l, F j, Y') }} · Fall 2026</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Your semester, {{ auth()->user()->name }}.</h1>
            <p class="mt-3 max-w-lg text-sm leading-6 text-white/80">You’re registered for 5 courses this semester. Check your schedule or explore courses that still have room.</p>
            <a href="#available-courses" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#2563eb] px-4 py-3 text-xs font-semibold text-white transition hover:bg-[#1d4ed8]">Explore courses →</a>
        </div>
        <div class="pointer-events-none absolute right-8 top-8 hidden h-40 w-40 rounded-full border border-white/10 sm:block"></div>
        <div class="pointer-events-none absolute right-16 top-16 hidden h-24 w-24 rounded-full border border-white/10 sm:block"></div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ([['Registered courses', '5', 'Fall semester 2026', '▤', 'bg-blue-50', 'text-[#2563eb]'], ['Credits this term', '15', 'Full-time study load', '✦', 'bg-amber-50', 'text-[#d97706]'], ['Next class', 'Today', 'Database Systems · 1:00 pm', '◷', 'bg-blue-50', 'text-[#2563eb]']] as [$label, $value, $detail, $icon, $iconBg, $iconColor])
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">{{ $label }}</span><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $iconBg }} text-lg {{ $iconColor }}">{{ $icon }}</span></div>
                <p class="mt-5 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p><p class="mt-1 text-xs text-slate-500">{{ $detail }}</p>
            </article>
        @endforeach
    </section>

    <section id="my-registrations" class="mt-8">
        <div class="mb-4 flex items-end justify-between">
            <div><h2 class="text-lg font-bold text-slate-900">My registrations</h2><p class="mt-1 text-xs text-slate-500">Your enrolled courses for Fall 2026</p></div>
            <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-semibold text-[#2563eb]">5 enrolled</span>
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[590px] text-left">
                    <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500"><tr><th class="px-6 py-3">Course</th><th class="px-4 py-3">Lecturer</th><th class="px-4 py-3">Schedule</th><th class="px-6 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach ([['CS101', 'Foundations of Computing', 'Dr. Elena Vasquez', 'Mon · 09:00', 'Confirmed'], ['CS204', 'Data Structures & Algorithms', 'Prof. James Okoro', 'Tue · 11:00', 'Confirmed'], ['CS220', 'Database Systems', 'Dr. Elena Vasquez', 'Wed · 13:00', 'Confirmed'], ['CS315', 'Human-Computer Interaction', 'Dr. Priya Nair', 'Thu · 10:00', 'Pending'], ['DS301', 'Machine Learning', 'Prof. James Okoro', 'Fri · 14:00', 'Confirmed']] as [$code, $name, $lecturer, $schedule, $status])
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4"><span class="block font-semibold text-slate-700">{{ $name }}</span><span class="mt-1 block text-[10px] text-slate-400">{{ $code }} · 3 credits</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $lecturer }}</td><td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $schedule }}</td>
                                <td class="px-6 py-4"><span class="status-pill {{ $status === 'Pending' ? 'status-pending' : 'status-active' }}">{{ $status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="available-courses" class="mt-8">
        <div class="mb-4"><h2 class="text-lg font-bold text-slate-900">Courses with open seats</h2><p class="mt-1 text-xs text-slate-500">Explore available courses for this semester</p></div>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ([['CS340', 'Software Architecture', 'Dr. Oliver Reed', 'Friday · 10:00', '8 seats left', 'bg-blue-50', 'text-[#2563eb]'], ['IS250', 'Digital Product Design', 'Dr. Priya Nair', 'Thursday · 14:00', '12 seats left', 'bg-amber-50', 'text-[#d97706]']] as [$code, $name, $lecturer, $schedule, $seats, $codeBg, $codeColor])
                <article class="flex flex-col justify-between gap-5 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center">
                    <div><span class="rounded-lg {{ $codeBg }} px-2.5 py-1.5 text-[10px] font-bold tracking-wide {{ $codeColor }}">{{ $code }}</span><h3 class="mt-4 text-sm font-bold text-slate-900">{{ $name }}</h3><p class="mt-1 text-xs text-slate-500">{{ $lecturer }} · {{ $schedule }}</p></div>
                    <div class="flex items-center justify-between gap-4 sm:flex-col sm:items-end"><span class="text-[10px] font-semibold text-[#d97706]">{{ $seats }}</span><button type="button" class="rounded-lg border border-slate-200 px-3 py-2 text-[10px] font-semibold text-slate-600 transition hover:border-blue-300 hover:text-[#2563eb]">View course</button></div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
