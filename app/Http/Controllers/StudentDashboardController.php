<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Registration;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

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

        return view('dashboard.course', [
            'role' => 'student',
            'course' => $course,
            'isRegistered' => $isRegistered,
        ]);
    }
}
