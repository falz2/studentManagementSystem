<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $student = $request->user()->studentProfile;
        $registrations = $student
            ? Registration::with(['course.lecturer.user'])
                ->where('student_id', $student->id)
                ->latest('registered_at')
                ->get()
            : collect();
        $availableCourses = Course::with(['lecturer.user'])
            ->withCount('registrations')
            ->where('status', 'open')
            ->when($student, fn ($query) => $query->whereDoesntHave(
                'registrations',
                fn ($registrations) => $registrations->where('student_id', $student->id)
            ))
            ->get()
            ->filter(fn (Course $course) => $course->registrations_count < $course->capacity)
            ->values();
        $pendingCount = $registrations->where('status', 'pending')->count();

        return view('dashboard.student', [
            'role' => 'student',
            'registrations' => $registrations,
            'availableCourses' => $availableCourses,
            'registeredCount' => $registrations->count(),
            'pendingCount' => $pendingCount,
        ]);
    }

    public function showCourse(Request $request, Course $course): View
    {
        $course->load('lecturer.user')->loadCount('registrations');

        $student = $request->user()->studentProfile;
        $isRegistered = $student !== null
            && $course->registrations()->where('student_id', $student->id)->exists();
        $isOpen = $course->status === 'open' && $course->registrations_count < $course->capacity;

        return view('dashboard.course', [
            'role' => 'student',
            'course' => $course,
            'isRegistered' => $isRegistered,
            'isOpen' => $isOpen,
        ]);
    }

    public function register(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();
        // Provision defaults for student accounts that do not yet have a profile.
        $student = $user->studentProfile()->firstOrCreate([], [
            'student_number' => 'STU-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'programme' => 'Undeclared',
            'year' => 1,
            'status' => 'active',
        ]);

        $result = DB::transaction(function () use ($course, $student): string {
            // Serialize capacity checks so concurrent requests cannot overfill the course.
            $course = Course::query()->withCount('registrations')->lockForUpdate()->findOrFail($course->id);

            if ($student->registrations()->where('course_id', $course->id)->exists()) {
                return 'already_registered';
            }

            if ($course->status !== 'open' || $course->registrations_count >= $course->capacity) {
                return 'unavailable';
            }

            $student->registrations()->create([
                'course_id' => $course->id,
                'registered_at' => today(),
                'status' => 'pending',
            ]);

            return 'registered';
        });

        $message = match ($result) {
            'registered' => ['success', 'Your registration request has been submitted.'],
            'already_registered' => ['error', 'You are already registered for this course.'],
            default => ['error', 'This course is not currently accepting registrations.'],
        };

        return to_route('student.courses.show', $course)->with(...$message);
    }
}
