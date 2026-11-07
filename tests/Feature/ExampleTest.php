<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Registration;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_jetstream_login_before_accessing_the_portal(): void
    {
        $this->get('/')->assertRedirect('/dashboard');
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin/students')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertSee('Create an account');
        $this->get('/register')->assertOk()->assertSee('created as students');
    }

    public function test_registration_always_creates_a_student_even_if_a_role_is_submitted(): void
    {
        $this->post('/register', [
            'name' => 'Taylor Student',
            'email' => 'taylor@example.edu',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'role' => 'admin',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', ['email' => 'taylor@example.edu']);
        $this->assertTrue(User::where('email', 'taylor@example.edu')->firstOrFail()->hasRole('student'));
    }

    public function test_students_can_only_access_their_own_dashboard(): void
    {
        $student = User::factory()->student()->create();
        $studentProfile = StudentProfile::create([
            'user_id' => $student->id,
            'student_number' => 'STU-001',
            'programme' => 'Computer Science',
            'year' => 2,
            'status' => 'active',
        ]);
        $course = Course::create([
            'name' => 'Database Systems',
            'code' => 'CS220',
            'schedule' => 'Wed 13:00',
            'capacity' => 30,
            'status' => 'open',
        ]);
        Registration::create([
            'student_id' => $studentProfile->id,
            'course_id' => $course->id,
            'registered_at' => '2026-11-04',
            'status' => 'confirmed',
        ]);
        $availableCourse = Course::create([
            'name' => 'Software Design',
            'code' => 'CS230',
            'schedule' => 'Fri 10:00',
            'capacity' => 20,
            'status' => 'open',
        ]);

        $this->actingAs($student)
            ->get('/dashboard')
            ->assertRedirect('/student');

        $this->get('/student')->assertOk()->assertSee('My registrations')->assertSee('Registered courses')
            ->assertSee('Database Systems')
            ->assertSee(route('student.courses.show', $availableCourse))
            ->assertDontSee(route('student.courses.show', $course));
        $this->get(route('student.courses.show', $course))
            ->assertOk()
            ->assertSee('Database Systems')
            ->assertSee('You are registered for this course.');
        $this->get('/lecturer')->assertForbidden();
        $this->get('/admin/students')->assertForbidden();
    }

    public function test_administrators_can_access_admin_pages_but_not_other_role_dashboards(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertRedirect('/admin');

        $this->get('/admin')->assertOk()
            ->assertSee('Course capacity')
            ->assertSee('Recent registrations')
            ->assertSee(route('admin.students.create'));
        $this->get('/admin/students')->assertOk()->assertSee('Student profiles');
        $this->get('/admin/courses')->assertOk()->assertSee('Course catalogue');
        $this->get('/admin/lecturers')->assertOk()->assertSee('Teaching staff');
        $this->get('/admin/registrations')->assertOk()->assertSee('Student enrolments');
        $this->get(route('student.dashboard'))->assertForbidden();
        $this->get('/lecturer')->assertForbidden();
        $this->get('/admin/unknown')->assertNotFound();
    }

    public function test_lecturers_are_redirected_to_their_own_dashboard(): void
    {
        $lecturer = User::factory()->lecturer()->create();

        $this->actingAs($lecturer)
            ->get('/dashboard')
            ->assertRedirect('/lecturer');

        $this->get('/lecturer')->assertOk()->assertSee('Your courses')->assertSee('Registered students');
        $this->get('/admin/courses')->assertForbidden();
    }
}
