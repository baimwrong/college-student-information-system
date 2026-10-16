<?php

use App\Http\Controllers\AcademicProgramController;
use App\Http\Controllers\AcademicTermController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BulkImportController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseSectionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Settings
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile.edit');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('profile.password');

    // Student Transcript (Admins, Staff, and the student themselves)
    Route::get('/students/{student}/transcript', [StudentController::class, 'transcript'])->name('students.transcript');

    // Grading Sheet (Admins, Staff, and assigned Instructor)
    Route::get('/sections/{section}/grades', [EnrollmentController::class, 'gradeSheet'])
        ->name('sections.grades')
        ->middleware('role:admin,staff,instructor');
    Route::post('/sections/{section}/grades', [EnrollmentController::class, 'saveGradeSheet'])
        ->name('sections.grades.save')
        ->middleware('role:admin,staff,instructor');

    // Class Sections / Timetable Viewing (Admins, Staff, and Instructors)
    Route::get('/sections', [CourseSectionController::class, 'index'])
        ->name('sections.index')
        ->middleware('role:admin,staff,instructor');
    Route::get('/sections/{section}', [CourseSectionController::class, 'show'])
        ->name('sections.show')
        ->middleware('role:admin,staff,instructor');

    // Admin & Staff Management Routes
    Route::middleware('role:admin,staff')->group(function () {
        // Departments
        Route::resource('departments', DepartmentController::class);

        // Academic Programs
        Route::resource('programs', AcademicProgramController::class);

        // Academic Terms
        Route::get('/terms', [AcademicTermController::class, 'index'])->name('terms.index');
        Route::post('/terms', [AcademicTermController::class, 'store'])->name('terms.store');
        Route::put('/terms/{term}', [AcademicTermController::class, 'update'])->name('terms.update');
        Route::post('/terms/{term}/set-current', [AcademicTermController::class, 'setCurrent'])->name('terms.setCurrent');
        Route::delete('/terms/{term}', [AcademicTermController::class, 'destroy'])->name('terms.destroy');

        // Classrooms
        Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
        Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::put('/classrooms/{classroom}', [ClassroomController::class, 'update'])->name('classrooms.update');
        Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');

        // Faculty / Instructors
        Route::resource('instructors', InstructorController::class);

        // Students Management
        Route::resource('students', StudentController::class)->except(['transcript']);

        // Courses Curriculum Catalog
        Route::resource('courses', CourseController::class);

        // Class Sections / Timetable Management (Create, Edit, Update, Destroy)
        Route::resource('sections', CourseSectionController::class)->except(['index', 'show']);

        // Enrollments
        Route::get('/enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/create', [EnrollmentController::class, 'create'])->name('enrollments.create');
        Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('enrollments.store');
        Route::delete('/enrollments/{enrollment}', [EnrollmentController::class, 'destroy'])->name('enrollments.destroy');

        // Admin Bulk Import & Export Hub
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/imports', [BulkImportController::class, 'index'])->name('imports.index');
            Route::get('/imports/template/{type}', [BulkImportController::class, 'template'])->name('imports.template');
            Route::post('/imports/{type}', [BulkImportController::class, 'import'])->name('imports.process');
            Route::get('/imports/log/{log}', [BulkImportController::class, 'showLog'])->name('imports.show');
            Route::get('/export/{type}', [BulkImportController::class, 'export'])->name('export');
        });
    });
});
