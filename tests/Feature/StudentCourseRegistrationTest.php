<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Registration;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCourseRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_student_can_register_for_an_open_course_once(): void
    {
        $student = $this->createStudent('STU-100');
        $course = $this->createCourse();

        $this->actingAs($student->user)
            ->get(route('student.courses.show', $course))
            ->assertOk()
            ->assertSee('Register for this course');

        $this->post(route('student.courses.register', $course))
            ->assertRedirect(route('student.courses.show', $course))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('registrations', [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'pending',
        ]);
        $this->assertSame(today()->toDateString(), Registration::firstOrFail()->registered_at->toDateString());

        $this->post(route('student.courses.register', $course))
            ->assertRedirect(route('student.courses.show', $course))
            ->assertSessionHas('error');

        $this->assertSame(1, Registration::count());
    }

    public function test_student_without_a_profile_can_register_for_a_course(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $course = $this->createCourse();

        $this->actingAs($user)
            ->post(route('student.courses.register', $course))
            ->assertRedirect(route('student.courses.show', $course))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('student_profiles', [
            'user_id' => $user->id,
            'student_number' => 'STU-'.str_pad((string) $user->id, 6, '0', STR_PAD_LEFT),
            'programme' => 'Undeclared',
        ]);
        $this->assertSame(1, Registration::count());
    }

    public function test_student_cannot_register_for_a_closed_or_full_course(): void
    {
        $student = $this->createStudent('STU-200');
        $otherStudent = $this->createStudent('STU-201');
        $closedCourse = $this->createCourse(['status' => 'closed']);
        $fullCourse = $this->createCourse(['code' => 'CS102', 'capacity' => 1]);

        Registration::create([
            'student_id' => $otherStudent->id,
            'course_id' => $fullCourse->id,
            'registered_at' => today(),
            'status' => 'confirmed',
        ]);

        $this->actingAs($student->user)
            ->post(route('student.courses.register', $closedCourse))
            ->assertSessionHas('error');

        $this->post(route('student.courses.register', $fullCourse))
            ->assertSessionHas('error');

        $this->assertSame(1, Registration::count());
    }

    private function createStudent(string $studentNumber): StudentProfile
    {
        return StudentProfile::create([
            'user_id' => User::factory()->create(['role' => 'student'])->id,
            'student_number' => $studentNumber,
            'programme' => 'Computing',
            'year' => 1,
            'status' => 'active',
        ]);
    }

    private function createCourse(array $attributes = []): Course
    {
        return Course::create(array_merge([
            'name' => 'Introduction to Computing',
            'code' => 'CS101',
            'schedule' => 'Mon 09:00',
            'capacity' => 2,
            'status' => 'open',
        ], $attributes));
    }
}