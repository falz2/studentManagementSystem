<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\LecturerProfile;
use App\Models\Registration;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminManagementCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_student(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('admin.students.store'), [
            'name' => 'Avery Student',
            'email' => 'avery@example.test',
            'password' => 'student-password',
            'student_number' => 'STU-100',
            'programme' => 'Computer Science',
            'year' => 2,
            'status' => 'active',
        ])->assertRedirect(route('admin.students'));

        $student = StudentProfile::with('user')->firstOrFail();
        $this->assertSame('student', $student->user->role);
        $this->assertDatabaseHas('student_profiles', ['student_number' => 'STU-100']);

        $this->put(route('admin.students.update', $student), [
            'name' => 'Avery Updated',
            'email' => 'avery@example.test',
            'password' => '',
            'student_number' => 'STU-100',
            'programme' => 'Data Science',
            'year' => 3,
            'status' => 'on_leave',
        ])->assertRedirect(route('admin.students'));

        $this->assertDatabaseHas('users', ['id' => $student->user_id, 'name' => 'Avery Updated']);
        $this->assertDatabaseHas('student_profiles', ['id' => $student->id, 'programme' => 'Data Science', 'year' => 3]);

        $this->delete(route('admin.students.destroy', $student))->assertRedirect(route('admin.students'));
        $this->assertDatabaseMissing('users', ['id' => $student->user_id]);
        $this->assertDatabaseMissing('student_profiles', ['id' => $student->id]);
    }

    public function test_admin_can_manage_lecturers_courses_and_registrations(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->post(route('admin.lecturers.store'), [
            'name' => 'Morgan Lecturer',
            'email' => 'morgan@example.test',
            'password' => 'lecturer-password',
            'staff_number' => 'LEC-100',
            'department' => 'Computing',
            'status' => 'active',
        ])->assertRedirect(route('admin.lecturers'));

        $lecturer = LecturerProfile::firstOrFail();
        $this->assertSame('lecturer', $lecturer->user->role);
        $student = StudentProfile::create([
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'student_number' => 'STU-200',
            'programme' => 'Computing',
            'year' => 1,
            'status' => 'active',
        ]);

        $this->post(route('admin.courses.store'), [
            'name' => 'Introduction to Computing',
            'code' => 'CS100',
            'lecturer_id' => $lecturer->id,
            'schedule' => 'Mon 09:00',
            'capacity' => 30,
            'status' => 'open',
        ])->assertRedirect(route('admin.courses'));

        $course = Course::firstOrFail();
        $this->put(route('admin.courses.update', $course), [
            'name' => 'Computing Fundamentals',
            'code' => 'CS100',
            'lecturer_id' => $lecturer->id,
            'schedule' => 'Tue 10:00',
            'capacity' => 35,
            'status' => 'open',
        ])->assertRedirect(route('admin.courses'));
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'name' => 'Computing Fundamentals', 'lecturer_id' => $lecturer->id]);

        $this->post(route('admin.registrations.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'registered_at' => '2026-11-03',
            'status' => 'confirmed',
        ])->assertRedirect(route('admin.registrations'));

        $registration = Registration::firstOrFail();
        $this->post(route('admin.registrations.store'), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'registered_at' => '2026-11-03',
            'status' => 'confirmed',
        ])->assertSessionHasErrors('student_id');

        $this->put(route('admin.registrations.update', $registration), [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'registered_at' => '2026-11-04',
            'status' => 'pending',
        ])->assertRedirect(route('admin.registrations'));
        $this->assertDatabaseHas('registrations', ['id' => $registration->id, 'status' => 'pending']);

        $this->delete(route('admin.registrations.destroy', $registration))->assertRedirect(route('admin.registrations'));
        $this->delete(route('admin.lecturers.destroy', $lecturer))->assertRedirect(route('admin.lecturers'));
        $this->assertDatabaseMissing('lecturer_profiles', ['id' => $lecturer->id]);
        $this->assertDatabaseHas('courses', ['id' => $course->id, 'lecturer_id' => null]);
        $this->delete(route('admin.courses.destroy', $course))->assertRedirect(route('admin.courses'));
    }

    public function test_non_admin_users_cannot_access_management_pages(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'student']));

        $this->get(route('admin.students'))->assertForbidden();
        $this->post(route('admin.students.store'))->assertForbidden();
    }
}
