<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\LecturerProfile;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function index(): View
    {
        $records = Course::with('lecturer.user')->withCount('registrations')->latest()->paginate(10)->through(fn (Course $course) => [
            'id' => $course->id,
            'cells' => [
                $course->name,
                $course->code,
                $course->lecturer?->user->name ?? 'Unassigned',
                $course->schedule,
                $course->registrations_count.' / '.$course->capacity,
                str($course->status)->title(),
            ],
        ]);

        return view('management.courses', ['records' => $records]);
    }

    public function create(): View
    {
        return view('management.form', [
            'title' => 'Add course',
            'action' => route('admin.courses.store'),
            'method' => 'POST',
            'cancel' => route('admin.courses'),
            'submit' => 'Create course',
            'fields' => $this->fields(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', 'unique:courses,code'],
            'lecturer_id' => ['nullable', 'exists:lecturer_profiles,id'],
            'schedule' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'between:1,65535'],
            'status' => ['required', Rule::in(['open', 'full', 'closed'])],
        ]);

        Course::create($data);

        return to_route('admin.courses')->with('success', 'Course created successfully.');
    }

    public function edit(Course $course): View
    {
        return view('management.form', [
            'title' => 'Edit course',
            'action' => route('admin.courses.update', $course),
            'method' => 'PUT',
            'cancel' => route('admin.courses'),
            'submit' => 'Save course',
            'fields' => $this->fields($course),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:255', Rule::unique('courses', 'code')->ignore($course->id)],
            'lecturer_id' => ['nullable', 'exists:lecturer_profiles,id'],
            'schedule' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'between:1,65535'],
            'status' => ['required', Rule::in(['open', 'full', 'closed'])],
        ]);

        $course->update($data);

        return to_route('admin.courses')->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $course->delete();

        return to_route('admin.courses')->with('success', 'Course deleted successfully.');
    }

    private function fields(?Course $course = null): array
    {
        $lecturers = LecturerProfile::with('user')->orderBy('staff_number')->get()
            ->mapWithKeys(fn (LecturerProfile $lecturer) => [$lecturer->id => $lecturer->user->name.' ('.$lecturer->staff_number.')'])
            ->all();

        return [
            ['name' => 'name', 'label' => 'Course name', 'value' => $course?->name, 'required' => true],
            ['name' => 'code', 'label' => 'Course code', 'value' => $course?->code, 'required' => true],
            ['name' => 'lecturer_id', 'label' => 'Lecturer', 'type' => 'select', 'value' => $course?->lecturer_id, 'options' => ['' => 'Unassigned'] + $lecturers],
            ['name' => 'schedule', 'label' => 'Schedule', 'value' => $course?->schedule, 'required' => true, 'placeholder' => 'e.g. Mon 09:00'],
            ['name' => 'capacity', 'label' => 'Capacity', 'type' => 'number', 'min' => 1, 'max' => 65535, 'value' => $course?->capacity, 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'value' => $course?->status ?? 'open', 'options' => ['open' => 'Open', 'full' => 'Full', 'closed' => 'Closed'], 'required' => true],
        ];
    }
}
