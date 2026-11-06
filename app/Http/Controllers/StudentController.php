<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(): View
    {
        $records = StudentProfile::with('user')->latest()->paginate(10)->through(fn (StudentProfile $student) => [
            'id' => $student->id,
            'cells' => [
                $student->user->name,
                $student->student_number,
                $student->user->email,
                $student->programme,
                'Year '.$student->year,
                str($student->status)->replace('_', ' ')->title(),
            ],
        ]);

        return view('management.students', ['records' => $records]);
    }

    public function create(): View
    {
        return view('management.form', [
            'title' => 'Add student',
            'action' => route('admin.students.store'),
            'method' => 'POST',
            'cancel' => route('admin.students'),
            'submit' => 'Create student',
            'fields' => $this->fields(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'student_number' => ['required', 'string', 'max:255', 'unique:student_profiles,student_number'],
            'programme' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'between:1,8'],
            'status' => ['required', Rule::in(['active', 'on_leave'])],
        ]);

        // Keep the login account and student profile in sync if either write fails.
        DB::transaction(function () use ($data): void {
            $user = User::query()->forceCreate([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'student',
            ]);

            $user->studentProfile()->create(collect($data)->only(['student_number', 'programme', 'year', 'status'])->all());
        });

        return to_route('admin.students')->with('success', 'Student created successfully.');
    }

    public function edit(StudentProfile $student): View
    {
        $student->load('user');

        return view('management.form', [
            'title' => 'Edit student',
            'action' => route('admin.students.update', $student),
            'method' => 'PUT',
            'cancel' => route('admin.students'),
            'submit' => 'Save student',
            'fields' => $this->fields($student),
        ]);
    }

    public function update(Request $request, StudentProfile $student): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($student->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'student_number' => ['required', 'string', 'max:255', Rule::unique('student_profiles', 'student_number')->ignore($student->id)],
            'programme' => ['required', 'string', 'max:255'],
            'year' => ['required', 'integer', 'between:1,8'],
            'status' => ['required', Rule::in(['active', 'on_leave'])],
        ]);

        DB::transaction(function () use ($data, $student): void {
            $student->user->update(collect($data)->only(['name', 'email'])->all());
            if (filled($data['password'])) {
                $student->user->update(['password' => $data['password']]);
            }
            $student->update(collect($data)->only(['student_number', 'programme', 'year', 'status'])->all());
        });

        return to_route('admin.students')->with('success', 'Student updated successfully.');
    }

    public function destroy(StudentProfile $student): RedirectResponse
    {
        $student->user->delete();

        return to_route('admin.students')->with('success', 'Student deleted successfully.');
    }

    private function fields(?StudentProfile $student = null): array
    {
        return [
            ['name' => 'name', 'label' => 'Full name', 'value' => $student?->user->name, 'required' => true],
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'value' => $student?->user->email, 'required' => true],
            ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => ! $student, 'hint' => $student ? 'Leave blank to keep the current password.' : 'At least 8 characters.'],
            ['name' => 'student_number', 'label' => 'Student number', 'value' => $student?->student_number, 'required' => true],
            ['name' => 'programme', 'label' => 'Programme', 'value' => $student?->programme, 'required' => true],
            ['name' => 'year', 'label' => 'Year of study', 'type' => 'number', 'min' => 1, 'max' => 8, 'value' => $student?->year, 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'value' => $student?->status ?? 'active', 'options' => ['active' => 'Active', 'on_leave' => 'On leave'], 'required' => true],
        ];
    }
}
