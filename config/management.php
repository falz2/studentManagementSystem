<?php

return [
    'students' => [
        'title' => 'Students',
        'description' => 'Student profiles and academic details.',
        'singular' => 'student',
        'columns' => ['Student', 'Student ID', 'Email', 'Programme', 'Year', 'Status'],
        'records' => [
            ['name' => 'Maya Chen', 'id' => 'STU-2024-0182', 'email' => 'maya.chen@northstar.edu', 'programme' => 'Computer Science', 'year' => 'Year 2', 'status' => 'Active'],
            ['name' => 'Ethan Brooks', 'id' => 'STU-2023-0094', 'email' => 'ethan.brooks@northstar.edu', 'programme' => 'Data Science', 'year' => 'Year 3', 'status' => 'Active'],
            ['name' => 'Amara Okafor', 'id' => 'STU-2024-0217', 'email' => 'amara.okafor@northstar.edu', 'programme' => 'Information Systems', 'year' => 'Year 2', 'status' => 'Active'],
            ['name' => 'Leo Martin', 'id' => 'STU-2022-0041', 'email' => 'leo.martin@northstar.edu', 'programme' => 'Computer Science', 'year' => 'Year 4', 'status' => 'On leave'],
            ['name' => 'Sofia Patel', 'id' => 'STU-2025-0028', 'email' => 'sofia.patel@northstar.edu', 'programme' => 'Software Engineering', 'year' => 'Year 1', 'status' => 'Active'],
        ],
    ],
    'courses' => [
        'title' => 'Courses',
        'description' => 'Course catalogue, capacity, and teaching assignments.',
        'singular' => 'course',
        'columns' => ['Course', 'Course code', 'Lecturer', 'Schedule', 'Enrolled', 'Status'],
        'records' => [
            ['name' => 'Foundations of Computing', 'id' => 'CS101', 'email' => 'Dr. Elena Vasquez', 'programme' => 'Mon · 09:00', 'year' => '32 / 40', 'status' => 'Open'],
            ['name' => 'Data Structures & Algorithms', 'id' => 'CS204', 'email' => 'Prof. James Okoro', 'programme' => 'Tue · 11:00', 'year' => '28 / 30', 'status' => 'Almost full'],
            ['name' => 'Database Systems', 'id' => 'CS220', 'email' => 'Dr. Elena Vasquez', 'programme' => 'Wed · 13:00', 'year' => '24 / 35', 'status' => 'Open'],
            ['name' => 'Human-Computer Interaction', 'id' => 'CS315', 'email' => 'Dr. Priya Nair', 'programme' => 'Thu · 10:00', 'year' => '18 / 25', 'status' => 'Open'],
            ['name' => 'Machine Learning', 'id' => 'DS301', 'email' => 'Prof. James Okoro', 'programme' => 'Fri · 14:00', 'year' => '30 / 30', 'status' => 'Full'],
        ],
    ],
    'lecturers' => [
        'title' => 'Lecturers',
        'description' => 'Teaching staff and their current course assignments.',
        'singular' => 'lecturer',
        'columns' => ['Lecturer', 'Staff ID', 'Email', 'Department', 'Courses', 'Status'],
        'records' => [
            ['name' => 'Dr. Elena Vasquez', 'id' => 'LEC-014', 'email' => 'e.vasquez@northstar.edu', 'programme' => 'Computer Science', 'year' => '3', 'status' => 'Active'],
            ['name' => 'Prof. James Okoro', 'id' => 'LEC-008', 'email' => 'j.okoro@northstar.edu', 'programme' => 'Data Science', 'year' => '2', 'status' => 'Active'],
            ['name' => 'Dr. Priya Nair', 'id' => 'LEC-021', 'email' => 'p.nair@northstar.edu', 'programme' => 'Information Systems', 'year' => '2', 'status' => 'Active'],
            ['name' => 'Dr. Oliver Reed', 'id' => 'LEC-017', 'email' => 'o.reed@northstar.edu', 'programme' => 'Computer Science', 'year' => '1', 'status' => 'On leave'],
        ],
    ],
    'registrations' => [
        'title' => 'Registrations',
        'description' => 'Student enrolments and their current registration status.',
        'singular' => 'registration',
        'columns' => ['Student', 'Student ID', 'Course', 'Course code', 'Registered', 'Status'],
        'records' => [
            ['name' => 'Maya Chen', 'id' => 'STU-2024-0182', 'email' => 'Database Systems', 'programme' => 'CS220', 'year' => 'Sep 02, 2026', 'status' => 'Confirmed'],
            ['name' => 'Ethan Brooks', 'id' => 'STU-2023-0094', 'email' => 'Machine Learning', 'programme' => 'DS301', 'year' => 'Sep 02, 2026', 'status' => 'Confirmed'],
            ['name' => 'Amara Okafor', 'id' => 'STU-2024-0217', 'email' => 'Human-Computer Interaction', 'programme' => 'CS315', 'year' => 'Sep 03, 2026', 'status' => 'Pending'],
            ['name' => 'Sofia Patel', 'id' => 'STU-2025-0028', 'email' => 'Foundations of Computing', 'programme' => 'CS101', 'year' => 'Sep 04, 2026', 'status' => 'Confirmed'],
            ['name' => 'Leo Martin', 'id' => 'STU-2022-0041', 'email' => 'Data Structures & Algorithms', 'programme' => 'CS204', 'year' => 'Sep 04, 2026', 'status' => 'Waitlisted'],
        ],
    ],
];
