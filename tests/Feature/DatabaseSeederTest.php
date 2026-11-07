<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_five_students_and_four_courses_and_is_repeatable(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(5, StudentProfile::count());
        $this->assertSame(5, User::role('student')->count());
        $this->assertSame(3, User::role('lecturer')->count());
        $this->assertSame(1, User::role('admin')->count());
        $this->assertSame(4, Course::count());
        $this->assertDatabaseHas('student_profiles', [
            'student_number' => 'STU-2024-0182',
            'programme' => 'Computer Science',
            'year' => 2,
        ]);
        $this->assertDatabaseHas('courses', [
            'code' => 'IS301',
            'name' => 'Database Systems',
            'capacity' => 30,
        ]);

        foreach ([
            'admin@northstar.edu' => 'admin',
            'elena.vasquez@northstar.edu' => 'lecturer',
            'maya.chen@northstar.edu' => 'student',
        ] as $email => $role) {
            $user = User::where('email', $email)->firstOrFail();
            $this->assertTrue($user->hasRole($role));
            $this->assertTrue(Hash::check('password', $user->password));
        }
    }
}
