@extends('layouts.portal')

@section('page-title', 'University overview')

@section('content')
    <div class="flex flex-col justify-between gap-5 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#d97706]">{{ now()->format('l, F j, Y') }}</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">Good morning, {{ auth()->user()->name }} <span class="text-[#d97706]" aria-hidden="true">✦</span></h1>
            <p class="mt-2 text-sm text-slate-500">Here’s what’s happening across your university today.</p>
        </div>
        <a href="{{ route('admin.students.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#2563eb] px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#1d4ed8]">
            <span class="text-lg leading-none" aria-hidden="true">+</span> Add a student
        </a>
    </div>

    <section class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="University statistics">
        @foreach ([['Students', '2,486', '+8.2%', 'Enrolled this academic year', '♙', 'bg-blue-50', 'text-[#2563eb]'], ['Active courses', '64', '+4 this term', 'Across 8 departments', '▤', 'bg-amber-50', 'text-[#d97706]'], ['Lecturers', '128', '94% assigned', 'Across all programmes', '♧', 'bg-blue-50', 'text-[#2563eb]'], ['Registrations', '1,842', '76 pending', 'Course registrations', '◎', 'bg-amber-50', 'text-[#d97706]']] as [$label, $value, $trend, $detail, $icon, $iconBg, $iconColor])
            <article class="rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex items-start justify-between">
                    <span class="text-sm font-medium text-slate-500">{{ $label }}</span>
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $iconBg }} text-lg {{ $iconColor }}" aria-hidden="true">{{ $icon }}</span>
                </div>
                <div class="mt-5 flex items-baseline gap-2">
                    <span class="text-3xl font-bold tracking-tight text-slate-900">{{ $value }}</span>
                    <span class="text-[11px] font-semibold text-[#2563eb]">{{ $trend }}</span>
                </div>
                <p class="mt-1 text-xs text-slate-400">{{ $detail }}</p>
            </article>
        @endforeach
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1.55fr_1fr]">
        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5 sm:px-6">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Recent registrations</h2>
                    <p class="mt-1 text-xs text-slate-400">Latest course enrolment activity</p>
                </div>
                <a href="{{ route('admin.registrations') }}" class="text-xs font-semibold text-[#2563eb] hover:text-[#1d4ed8]">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[580px] text-left">
                    <thead class="bg-slate-50 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                        <tr><th class="px-6 py-3">Student</th><th class="px-4 py-3">Course</th><th class="px-4 py-3">Date</th><th class="px-6 py-3">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs">
                        @foreach ([['Maya Chen', 'MC', 'Database Systems', 'CS220', 'Today, 9:42 am', 'Confirmed'], ['Ethan Brooks', 'EB', 'Machine Learning', 'DS301', 'Today, 8:16 am', 'Confirmed'], ['Amara Okafor', 'AO', 'Human-Computer Interaction', 'CS315', 'Yesterday', 'Pending'], ['Sofia Patel', 'SP', 'Foundations of Computing', 'CS101', 'Yesterday', 'Confirmed']] as [$name, $initials, $course, $code, $date, $status])
                            <tr class="hover:bg-slate-50">
                                <td class="whitespace-nowrap px-6 py-4"><span class="flex items-center gap-3"><span class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-50 text-[10px] font-bold text-[#2563eb]">{{ $initials }}</span><span class="font-semibold text-slate-700">{{ $name }}</span></span></td>
                                <td class="px-4 py-4"><span class="block font-medium text-slate-700">{{ $course }}</span><span class="mt-1 block text-[10px] text-slate-400">{{ $code }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-slate-500">{{ $date }}</td>
                                <td class="px-6 py-4"><span class="status-pill {{ $status === 'Pending' ? 'status-pending' : 'status-active' }}">{{ $status }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-6 py-3 text-center">
                <a href="{{ route('admin.registrations') }}" class="text-xs font-semibold text-slate-500 hover:text-[#2563eb]">See all registrations</a>
            </div>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 sm:p-6">
            <div class="flex items-start justify-between">
                <div><h2 class="text-base font-bold text-slate-900">Course capacity</h2><p class="mt-1 text-xs text-slate-400">Popular courses this term</p></div>
                <a href="{{ route('admin.courses') }}" class="text-xs font-semibold text-[#2563eb]">All courses</a>
            </div>
            <div class="mt-6 space-y-5">
                @foreach ([['Data Structures & Algorithms', 'CS204', 93, '28 / 30'], ['Machine Learning', 'DS301', 100, '30 / 30'], ['Foundations of Computing', 'CS101', 80, '32 / 40'], ['Database Systems', 'CS220', 69, '24 / 35']] as [$course, $code, $percent, $count])
                    <div>
                        <div class="flex items-center justify-between gap-3 text-xs">
                            <div class="min-w-0"><p class="truncate font-semibold text-slate-700">{{ $course }}</p><p class="mt-1 text-[10px] text-slate-400">{{ $code }}</p></div>
                            <span class="shrink-0 text-slate-500">{{ $count }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $percent === 100 ? 'bg-[#d97706]' : 'bg-[#2563eb]' }}" style="width: {{ $percent }}%"></div></div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Management shortcuts">
        @foreach ([['Students', 'Manage student profiles', 'students', '♙'], ['Courses', 'Review the course catalogue', 'courses', '▤'], ['Lecturers', 'View teaching assignments', 'lecturers', '♧'], ['Registrations', 'Review course enrolments', 'registrations', '◎']] as [$title, $description, $section, $icon])
            <a href="{{ route('admin.'.$section) }}" class="group flex items-center gap-4 rounded-xl border border-slate-200 bg-white p-4 transition hover:-translate-y-0.5 hover:border-blue-200 hover:shadow-md">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg {{ $loop->even ? 'bg-amber-50 text-[#d97706]' : 'bg-blue-50 text-[#2563eb]' }}">{{ $icon }}</span>
                <span class="min-w-0 flex-1"><span class="block text-sm font-semibold text-slate-900">{{ $title }}</span><span class="mt-1 block truncate text-xs text-slate-400">{{ $description }}</span></span>
                <span class="text-slate-300 transition group-hover:translate-x-1 group-hover:text-[#2563eb]" aria-hidden="true">→</span>
            </a>
        @endforeach
    </section>

    <section class="mt-8 rounded-xl border border-amber-100 bg-amber-50 p-5 sm:flex sm:items-center sm:justify-between sm:px-7">
        <div class="flex items-start gap-4">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white text-[#d97706]">✦</span>
            <div><p class="text-sm font-semibold text-slate-900">Registration period is open</p><p class="mt-1 text-xs leading-5 text-slate-600">Students can register for courses until September 19. There are 76 registrations awaiting review.</p></div>
        </div>
        <a href="{{ route('admin.registrations') }}" class="ml-14 mt-3 inline-flex text-xs font-bold text-[#d97706] hover:underline sm:ml-4 sm:mt-0">Review registrations →</a>
    </section>
@endsection
