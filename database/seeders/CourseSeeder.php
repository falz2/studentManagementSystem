<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'code' => 'CS101',
                'name' => 'Foundations of Computing',
                'lecturer' => 'Dr. Elena Vasquez',
                'schedule' => 'Mon · 09:00',
                'capacity' => 40,
                'enrolled' => 32,
                'status' => 'open',
            ],
            [
                'code' => 'CS204',
                'name' => 'Data Structures & Algorithms',
                'lecturer' => 'Prof. James Okoro',
                'schedule' => 'Tue · 11:00',
                'capacity' => 30,
                'enrolled' => 28,
                'status' => 'almost full',
            ],
            [
                'code' => 'CS220',
                'name' => 'Database Systems',
                'lecturer' => 'Dr. Elena Vasquez',
                'schedule' => 'Wed · 13:00',
                'capacity' => 35,
                'enrolled' => 24,
                'status' => 'open',
            ],
            [
                'code' => 'CS315',
                'name' => 'Human-Computer Interaction',
                'lecturer' => 'Dr. Priya Nair',
                'schedule' => 'Thu · 10:00',
                'capacity' => 25,
                'enrolled' => 18,
                'status' => 'open',
            ],
        ];

        foreach ($courses as $course) {
            Course::updateOrCreate(
                ['code' => $course['code']],
                $course,
            );
        }
    }
}
