<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\LecturerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // Create lecturer accounts and profiles first so each course can reference its lecturer.
        $lecturers = [
            [
                'name' => 'Dr. Elena Vasquez',
                'email' => 'elena.vasquez@northstar.edu',
                'staff_number' => 'LEC-0001',
                'department' => 'Computer Science',
            ],
            [
                'name' => 'Prof. Samuel Okafor',
                'email' => 'samuel.okafor@northstar.edu',
                'staff_number' => 'LEC-0002',
                'department' => 'Mathematics',
            ],
            [
                'name' => 'Dr. Aisha Rahman',
                'email' => 'aisha.rahman@northstar.edu',
                'staff_number' => 'LEC-0003',
                'department' => 'Information Systems',
            ],
        ];

        $lecturerProfiles = [];

        foreach ($lecturers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'role' => 'lecturer',
                ]
            );

            $lecturerProfiles[$data['email']] = LecturerProfile::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'staff_number' => $data['staff_number'],
                    'department' => $data['department'],
                    'status' => 'active',
                ]
            );
        }

        // Resolve lecturer profiles by email, then upsert courses by their unique code.
        $courses = [
            [
                'code' => 'CS101',
                'name' => 'Foundations of Computing',
                'lecturer_email' => 'elena.vasquez@northstar.edu',
                'schedule' => 'Mon · 09:00',
                'capacity' => 40,
                'status' => 'open',
            ],
            [
                'code' => 'CS201',
                'name' => 'Data Structures & Algorithms',
                'lecturer_email' => 'elena.vasquez@northstar.edu',
                'schedule' => 'Tue · 11:00',
                'capacity' => 35,
                'status' => 'open',
            ],
            [
                'code' => 'MTH101',
                'name' => 'Discrete Mathematics',
                'lecturer_email' => 'samuel.okafor@northstar.edu',
                'schedule' => 'Wed · 08:00',
                'capacity' => 50,
                'status' => 'open',
            ],
            [
                'code' => 'IS301',
                'name' => 'Database Systems',
                'lecturer_email' => 'aisha.rahman@northstar.edu',
                'schedule' => 'Thu · 14:00',
                'capacity' => 30,
                'status' => 'open',
            ],
        ];

        foreach ($courses as $data) {
            Course::updateOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'lecturer_id' => $lecturerProfiles[$data['lecturer_email']]->id,
                    'schedule' => $data['schedule'],
                    'capacity' => $data['capacity'],
                    'status' => $data['status'],
                ]
            );
        }
    }
}