<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
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
                'year' => 2,
            ],
            [
                'name' => 'Jordan Okello',
                'email' => 'jordan.okello@northstar.edu',
                'student_number' => 'STU-2024-0183',
                'programme' => 'Information Systems',
                'year' => 1,
            ],
            [
                'name' => 'Amara Ndlovu',
                'email' => 'amara.ndlovu@northstar.edu',
                'student_number' => 'STU-2023-0094',
                'programme' => 'Mathematics',
                'year' => 3,
            ],
            [
                'name' => 'Leo Kimani',
                'email' => 'leo.kimani@northstar.edu',
                'student_number' => 'STU-2024-0210',
                'programme' => 'Computer Science',
                'year' => 2,
            ],
            [
                'name' => 'Priya Patel',
                'email' => 'priya.patel@northstar.edu',
                'student_number' => 'STU-2022-0061',
                'programme' => 'Information Systems',
                'year' => 4,
            ],
        ];

        foreach ($students as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );
            $user->syncRoles('student');

            StudentProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'student_number' => $data['student_number'],
                    'programme' => $data['programme'],
                    'year' => $data['year'],
                    'status' => 'active',
                ]
            );
        }
    }
}
