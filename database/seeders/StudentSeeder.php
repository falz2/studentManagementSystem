<?php

namespace Database\Seeders;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
                'status' => 'active',
            ],
        ];

        foreach ($students as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'role' => 'student',
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'student_number' => $data['student_number'],
                'programme' => $data['programme'],
                'year' => $data['year'],
                'status' => $data['status'],
            ]);
        }
    }
}