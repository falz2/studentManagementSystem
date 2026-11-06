<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Registration;
use App\Models\StudentProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegistrationController extends Controller
{
    public function index(): View
    {
        $records = Registration::with(['student.user', 'course'])->latest('registered_at')->paginate(10)->through(fn (Registration $registration) => [
            'id' => $registration->id,
            'cells' => [
                $registration->student->user->name,
                $registration->student->student_number,
                $registration->course->name,
                $registration->course->code,
                $registration->registered_at->format('M d, Y'),
                str($registration->status)->title(),
            ],
        ]);

        return view('management.registrations', ['records' => $records]);
    }

    public function create(): View
    {
        return view('management.form', [
            'title' => 'Add registration',
            'action' => route('admin.registrations.store'),
            'method' => 'POST',
            'cancel' => route('admin.registrations'),
            'submit' => 'Create registration',
            'fields' => $this->fields(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules($request));
        Registration::create($data);

        return to_route('admin.registrations')->with('success', 'Registration created successfully.');
    }

    public function edit(Registration $registration): View
    {
        return view('management.form', [
            'title' => 'Edit registration',
            'action' => route('admin.registrations.update', $registration),
            'method' => 'PUT',
            'cancel' => route('admin.registrations'),
            'submit' => 'Save registration',
            'fields' => $this->fields($registration),
        ]);
    }

    public function update(Request $request, Registration $registration): RedirectResponse
    {
        $registration->update($request->validate($this->rules($request, $registration)));

        return to_route('admin.registrations')->with('success', 'Registration updated successfully.');
    }

    public function destroy(Registration $registration): RedirectResponse
    {
        $registration->delete();

        return to_route('admin.registrations')->with('success', 'Registration deleted successfully.');
    }

    private function rules(Request $request, ?Registration $registration = null): array
    {
        return [
            'student_id' => [
                'required',
                'exists:student_profiles,id',
                // A student may register for multiple courses, but only once per course.
                Rule::unique('registrations', 'student_id')
                    ->where('course_id', $request->input('course_id'))
                    ->ignore($registration?->id),
            ],
            'course_id' => ['required', 'exists:courses,id'],
            'registered_at' => ['required', 'date'],
            'status' => ['required', Rule::in(['confirmed', 'pending', 'waitlisted'])],
        ];
    }

    private function fields(?Registration $registration = null): array
    {
        $students = StudentProfile::with('user')->orderBy('student_number')->get()
            ->mapWithKeys(fn (StudentProfile $student) => [$student->id => $student->user->name.' ('.$student->student_number.')'])
            ->all();
        $courses = Course::orderBy('code')->get()
            ->mapWithKeys(fn (Course $course) => [$course->id => $course->name.' ('.$course->code.')'])
            ->all();

        return [
            ['name' => 'student_id', 'label' => 'Student', 'type' => 'select', 'value' => $registration?->student_id, 'options' => ['' => 'Select a student'] + $students, 'required' => true],
            ['name' => 'course_id', 'label' => 'Course', 'type' => 'select', 'value' => $registration?->course_id, 'options' => ['' => 'Select a course'] + $courses, 'required' => true],
            ['name' => 'registered_at', 'label' => 'Registration date', 'type' => 'date', 'value' => $registration?->registered_at->format('Y-m-d') ?? now()->toDateString(), 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'value' => $registration?->status ?? 'confirmed', 'options' => ['confirmed' => 'Confirmed', 'pending' => 'Pending', 'waitlisted' => 'Waitlisted'], 'required' => true],
        ];
    }
}
