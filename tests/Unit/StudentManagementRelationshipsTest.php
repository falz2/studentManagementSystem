<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Lecturer;
use App\Models\Registration;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentManagementRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_course_and_lecturer_relationships_are_configured(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $lecturerUser = User::factory()->create(['role' => 'lecturer']);

        $student = Student::create([
            'user_id' => $user->id,
            'student_number' => 'STU-2024-0182',
            'name' => 'Maya Chen',
            'email' => 'maya.chen@northstar.edu',
            'programme' => 'Computer Science',
            'year_level' => 'Year 2',
            'status' => 'active',
        ]);

        $lecturer = Lecturer::create([
            'user_id' => $lecturerUser->id,
            'staff_number' => 'LEC-014',
            'name' => 'Dr. Elena Vasquez',
            'email' => 'e.vasquez@northstar.edu',
            'department' => 'Computer Science',
            'status' => 'active',
        ]);

        $course = Course::create([
            'lecturer_id' => $lecturer->id,
            'code' => 'CS220',
            'name' => 'Database Systems',
            'schedule' => 'Wed · 13:00',
            'capacity' => 35,
            'status' => 'open',
        ]);

        $registration = Registration::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'status' => 'confirmed',
            'registered_at' => now(),
        ]);

        $this->assertSame($user->id, $student->user->id);
        $this->assertTrue($student->registrations->contains($registration));
        $this->assertTrue($student->courses->contains($course));

        $this->assertSame($lecturer->id, $course->lecturer->id);
        $this->assertTrue($lecturer->courses->contains($course));
        $this->assertTrue($course->students->contains($student));

        $this->assertSame($student->id, $registration->student->id);
        $this->assertSame($course->id, $registration->course->id);
    }
}
