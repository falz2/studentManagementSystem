<?php

namespace App\Http\Controllers;

use App\Models\LecturerProfile;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class LecturerController extends Controller
{
    public function index(): View
    {
        $records = LecturerProfile::with(['user', 'courses'])->latest()->paginate(10)->through(fn (LecturerProfile $lecturer) => [
            'id' => $lecturer->id,
            'cells' => [
                $lecturer->user->name,
                $lecturer->staff_number,
                $lecturer->user->email,
                $lecturer->department,
                $lecturer->courses->count(),
                str($lecturer->status)->replace('_', ' ')->title(),
            ],
        ]);

        return view('management.lecturers', ['records' => $records]);
    }

    public function create(): View
    {
        return view('management.form', [
            'title' => 'Add lecturer',
            'action' => route('admin.lecturers.store'),
            'method' => 'POST',
            'cancel' => route('admin.lecturers'),
            'submit' => 'Create lecturer',
            'fields' => $this->fields(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'staff_number' => ['required', 'string', 'max:255', 'unique:lecturer_profiles,staff_number'],
            'department' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'on_leave'])],
        ]);

        // Keep the login account and lecturer profile in sync if either write fails.
        DB::transaction(function () use ($data): void {
            $user = User::query()->forceCreate([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);
            $user->assignRole('lecturer');

            $user->lecturerProfile()->create(collect($data)->only(['staff_number', 'department', 'status'])->all());
        });

        return to_route('admin.lecturers')->with('success', 'Lecturer created successfully.');
    }

    public function edit(LecturerProfile $lecturer): View
    {
        $lecturer->load('user');

        return view('management.form', [
            'title' => 'Edit lecturer',
            'action' => route('admin.lecturers.update', $lecturer),
            'method' => 'PUT',
            'cancel' => route('admin.lecturers'),
            'submit' => 'Save lecturer',
            'fields' => $this->fields($lecturer),
        ]);
    }

    public function update(Request $request, LecturerProfile $lecturer): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($lecturer->user_id)],
            'password' => ['nullable', 'string', 'min:8'],
            'staff_number' => ['required', 'string', 'max:255', Rule::unique('lecturer_profiles', 'staff_number')->ignore($lecturer->id)],
            'department' => ['required', 'string', 'max:255'],
            'status' => ['required', Rule::in(['active', 'on_leave'])],
        ]);

        DB::transaction(function () use ($data, $lecturer): void {
            $lecturer->user->update(collect($data)->only(['name', 'email'])->all());
            if (filled($data['password'])) {
                $lecturer->user->update(['password' => $data['password']]);
            }
            $lecturer->update(collect($data)->only(['staff_number', 'department', 'status'])->all());
        });

        return to_route('admin.lecturers')->with('success', 'Lecturer updated successfully.');
    }

    public function destroy(LecturerProfile $lecturer): RedirectResponse
    {
        $lecturer->user->delete();

        return to_route('admin.lecturers')->with('success', 'Lecturer deleted successfully.');
    }

    private function fields(?LecturerProfile $lecturer = null): array
    {
        return [
            ['name' => 'name', 'label' => 'Full name', 'value' => $lecturer?->user->name, 'required' => true],
            ['name' => 'email', 'label' => 'Email address', 'type' => 'email', 'value' => $lecturer?->user->email, 'required' => true],
            ['name' => 'password', 'label' => 'Password', 'type' => 'password', 'required' => ! $lecturer, 'hint' => $lecturer ? 'Leave blank to keep the current password.' : 'At least 8 characters.'],
            ['name' => 'staff_number', 'label' => 'Staff number', 'value' => $lecturer?->staff_number, 'required' => true],
            ['name' => 'department', 'label' => 'Department', 'value' => $lecturer?->department, 'required' => true],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'value' => $lecturer?->status ?? 'active', 'options' => ['active' => 'Active', 'on_leave' => 'On leave'], 'required' => true],
        ];
    }
}
