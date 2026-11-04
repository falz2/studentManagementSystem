<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'name' => 'Maya Chen',
                'email' => 'maya.chen@northstar.edu',
                'student_number' => 'STU-2024-0182',
                'programme' => 'Computer Science',
                'year_of_study' => 2,
                'student_status' => 'active',
            ],
            [
                'name' => 'Ethan Brooks',
                'email' => 'ethan.brooks@northstar.edu',
                'student_number' => 'STU-2023-0094',
                'programme' => 'Data Science',
                'year_of_study' => 3,
                'student_status' => 'active',
            ],
            [
                'name' => 'Amara Okafor',
                'email' => 'amara.okafor@northstar.edu',
                'student_number' => 'STU-2024-0217',
                'programme' => 'Information Systems',
                'year_of_study' => 2,
                'student_status' => 'active',
            ],
            [
                'name' => 'Leo Martin',
                'email' => 'leo.martin@northstar.edu',
                'student_number' => 'STU-2022-0041',
                'programme' => 'Computer Science',
                'year_of_study' => 4,
                'student_status' => 'on leave',
            ],
            [
                'name' => 'Sofia Patel',
                'email' => 'sofia.patel@northstar.edu',
                'student_number' => 'STU-2025-0028',
                'programme' => 'Software Engineering',
                'year_of_study' => 1,
                'student_status' => 'active',
            ],
        ];

        foreach ($students as $student) {
            $user = User::firstOrNew(['email' => $student['email']]);
            $user->forceFill([
                ...$student,
                'role' => 'student',
                'email_verified_at' => now(),
                'password' => 'password',
            ])->save();
        }
    }
}
