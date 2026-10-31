<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LecturerController;
use App\Http\Controllers\LecturerDashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDashboardController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students');
        Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.courses');
        Route::get('/admin/lecturers', [LecturerController::class, 'index'])->name('admin.lecturers');
        Route::get('/admin/registrations', [RegistrationController::class, 'index'])->name('admin.registrations');
    });

    Route::get('/lecturer', [LecturerDashboardController::class, 'index'])
        ->middleware('role:lecturer')
        ->name('lecturer.dashboard');

    Route::get('/student', [StudentDashboardController::class, 'index'])
        ->middleware('role:student')
        ->name('student.dashboard');
});
