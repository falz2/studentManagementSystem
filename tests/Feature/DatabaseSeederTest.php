<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_five_students_and_four_courses_and_is_repeatable(): void
    {
        $this->seed();
        $this->seed();

        $this->assertSame(5, User::where('role', 'student')->count());
        $this->assertSame(4, Course::count());
        $this->assertDatabaseHas('users', [
            'email' => 'maya.chen@northstar.edu',
            'student_number' => 'STU-2024-0182',
            'programme' => 'Computer Science',
            'year_of_study' => 2,
        ]);
        $this->assertDatabaseHas('courses', [
            'code' => 'CS220',
            'name' => 'Database Systems',
            'capacity' => 35,
            'enrolled' => 24,
        ]);
    }
}
