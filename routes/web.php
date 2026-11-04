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
        Route::get('/admin/students/create', [StudentController::class, 'create'])->name('admin.students.create');
        Route::post('/admin/students', [StudentController::class, 'store'])->name('admin.students.store');
        Route::get('/admin/students/{student}/edit', [StudentController::class, 'edit'])->name('admin.students.edit');
        Route::put('/admin/students/{student}', [StudentController::class, 'update'])->name('admin.students.update');
        Route::delete('/admin/students/{student}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
        Route::get('/admin/courses', [CourseController::class, 'index'])->name('admin.courses');
        Route::get('/admin/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
        Route::post('/admin/courses', [CourseController::class, 'store'])->name('admin.courses.store');
        Route::get('/admin/courses/{course}/edit', [CourseController::class, 'edit'])->name('admin.courses.edit');
        Route::put('/admin/courses/{course}', [CourseController::class, 'update'])->name('admin.courses.update');
        Route::delete('/admin/courses/{course}', [CourseController::class, 'destroy'])->name('admin.courses.destroy');
        Route::get('/admin/lecturers', [LecturerController::class, 'index'])->name('admin.lecturers');
        Route::get('/admin/lecturers/create', [LecturerController::class, 'create'])->name('admin.lecturers.create');
        Route::post('/admin/lecturers', [LecturerController::class, 'store'])->name('admin.lecturers.store');
        Route::get('/admin/lecturers/{lecturer}/edit', [LecturerController::class, 'edit'])->name('admin.lecturers.edit');
        Route::put('/admin/lecturers/{lecturer}', [LecturerController::class, 'update'])->name('admin.lecturers.update');
        Route::delete('/admin/lecturers/{lecturer}', [LecturerController::class, 'destroy'])->name('admin.lecturers.destroy');
        Route::get('/admin/registrations', [RegistrationController::class, 'index'])->name('admin.registrations');
        Route::get('/admin/registrations/create', [RegistrationController::class, 'create'])->name('admin.registrations.create');
        Route::post('/admin/registrations', [RegistrationController::class, 'store'])->name('admin.registrations.store');
        Route::get('/admin/registrations/{registration}/edit', [RegistrationController::class, 'edit'])->name('admin.registrations.edit');
        Route::put('/admin/registrations/{registration}', [RegistrationController::class, 'update'])->name('admin.registrations.update');
        Route::delete('/admin/registrations/{registration}', [RegistrationController::class, 'destroy'])->name('admin.registrations.destroy');
    });

    Route::get('/lecturer', [LecturerDashboardController::class, 'index'])
        ->middleware('role:lecturer')
        ->name('lecturer.dashboard');

    Route::get('/student', [StudentDashboardController::class, 'index'])
        ->middleware('role:student')
        ->name('student.dashboard');
});
