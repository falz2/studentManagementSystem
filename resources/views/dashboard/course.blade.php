@extends('layouts.portal')

@section('page-title', $course->name)
@section('breadcrumb', 'Course details')

@section('content')
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('student.dashboard') }}#available-courses" class="text-sm font-semibold text-[#2563eb] hover:underline">← Back to courses</a>
        <section class="mt-5 rounded-xl border border-slate-200 bg-white p-6 sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#d97706]">{{ $course->code }}</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-slate-900">{{ $course->name }}</h1>
            <dl class="mt-8 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Lecturer</dt><dd class="mt-1 text-sm text-slate-700">{{ $course->lecturer?->user->name ?? 'Unassigned' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Schedule</dt><dd class="mt-1 text-sm text-slate-700">{{ $course->schedule }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Enrollment</dt><dd class="mt-1 text-sm text-slate-700">{{ $course->registrations_count }} / {{ $course->capacity }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</dt><dd class="mt-1 text-sm text-slate-700">{{ str($course->status)->title() }}</dd></div>
            </dl>
            @if ($isRegistered)
                <p role="status" class="mt-8 rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-800">{{ session('success', 'You are already registered for this course.') }}</p>
            @elseif (! $isOpen)
                <p role="status" class="mt-8 rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ session('error', 'This course is not currently accepting registrations.') }}</p>
            @else
                @if (session('error'))
                    <p role="alert" class="mt-8 rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">{{ session('error') }}</p>
                @endif
                <form method="POST" action="{{ route('student.courses.register', $course) }}" class="mt-8">
                    @csrf
                    <button type="submit" class="rounded-lg bg-[#2563eb] px-5 py-3 text-sm font-semibold text-white hover:bg-[#1d4ed8] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Register for this course</button>
                </form>
            @endif
        </section>
    </div>
@endsection
