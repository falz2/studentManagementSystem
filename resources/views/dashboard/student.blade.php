@extends('layouts.portal')

@section('page-title', 'Student dashboard')

@section('content')
    <section class="relative overflow-hidden rounded-2xl bg-[#1e3a8a] px-6 py-8 text-white sm:px-9 sm:py-10">
        <div class="relative z-10 max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#fbbf24]">{{ now()->format('l, F j, Y') }}</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Your semester, {{ auth()->user()->name }}.</h1>
            <p class="mt-3 max-w-lg text-sm leading-6 text-white/80">Check your course registrations or explore courses that still have room.</p>
            <a href="#available-courses" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#2563eb] px-4 py-3 text-xs font-semibold text-white transition hover:bg-[#1d4ed8]">Explore courses →</a>
        </div>
        <div class="pointer-events-none absolute right-8 top-8 hidden h-40 w-40 rounded-full border border-white/10 sm:block"></div>
        <div class="pointer-events-none absolute right-16 top-16 hidden h-24 w-24 rounded-full border border-white/10 sm:block"></div>
    </section>

    <section class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ([['Registered courses', $registeredCount, 'Your current registrations', '▤', 'bg-blue-50', 'text-[#2563eb]'], ['Courses with open seats', $availableCourses->count(), 'Available to explore', '✦', 'bg-amber-50', 'text-[#d97706]'], ['Pending registrations', $pendingCount, 'Awaiting confirmation', '◷', 'bg-blue-50', 'text-[#2563eb]']] as [$label, $value, $detail, $icon, $iconBg, $iconColor])
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-center justify-between"><span class="text-sm font-medium text-slate-500">{{ $label }}</span><span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $iconBg }} text-lg {{ $iconColor }}">{{ $icon }}</span></div>
                <p class="mt-5 text-2xl font-bold tracking-tight text-slate-900">{{ $value }}</p><p class="mt-1 text-xs text-slate-500">{{ $detail }}</p>
            </article>
        @endforeach
    </section>

    <section id="my-registrations" class="mt-8">
        <div class="mb-4 flex items-end justify-between">
            <div><h2 class="text-lg font-bold text-slate-900">My registrations</h2><p class="mt-1 text-xs text-slate-500">Your registered courses</p></div>
            <span class="rounded-full border border-blue-100 bg-blue-50 px-3 py-1.5 text-[10px] font-semibold text-[#2563eb]">{{ $registeredCount }} enrolled</span>
        </div>
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[590px] text-left">
                    <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500"><tr><th class="px-6 py-3">Course</th><th class="px-4 py-3">Lecturer</th><th class="px-4 py-3">Schedule</th><th class="px-6 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @forelse ($registrations as $registration)
                            <tr class="hover:bg-slate-50">
                                <td class="px-6 py-4"><span class="block font-semibold text-slate-700">{{ $registration->course->name }}</span><span class="mt-1 block text-[10px] text-slate-400">{{ $registration->course->code }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $registration->course->lecturer?->user->name ?? 'Unassigned' }}</td><td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $registration->course->schedule }}</td>
                                <td class="px-6 py-4"><span class="status-pill {{ $registration->status === 'pending' ? 'status-pending' : 'status-active' }}">{{ str($registration->status)->title() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">You do not have any course registrations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <section id="available-courses" class="mt-8">
        <div class="mb-4"><h2 class="text-lg font-bold text-slate-900">Courses with open seats</h2><p class="mt-1 text-xs text-slate-500">Explore courses that are currently open and have capacity</p></div>
        <div class="grid gap-4 md:grid-cols-2">
            @forelse ($availableCourses as $course)
                <article class="flex flex-col justify-between gap-5 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center">
                    <div><span class="rounded-lg bg-blue-50 px-2.5 py-1.5 text-[10px] font-bold tracking-wide text-[#2563eb]">{{ $course->code }}</span><h3 class="mt-4 text-sm font-bold text-slate-900">{{ $course->name }}</h3><p class="mt-1 text-xs text-slate-500">{{ $course->lecturer?->user->name ?? 'Unassigned' }} · {{ $course->schedule }}</p></div>
                    <div class="flex items-center justify-between gap-4 sm:flex-col sm:items-end"><span class="text-[10px] font-semibold text-[#d97706]">{{ $course->capacity - $course->registrations_count }} {{ \Illuminate\Support\Str::plural('seat', $course->capacity - $course->registrations_count) }} left</span><a href="{{ route('student.courses.show', $course) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-center text-[10px] font-semibold text-slate-600 transition hover:border-blue-300 hover:text-[#2563eb]">View course</a></div>
                </article>
            @empty
                <p class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500 md:col-span-2">There are no courses with open seats right now.</p>
            @endforelse
        </div>
    </section>
@endsection
