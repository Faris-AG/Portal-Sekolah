<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ScheduleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    $role = auth()->user()->role;
    if ($role === 'admin') return redirect()->route('admin.dashboard');
    if ($role === 'guru') return redirect()->route('guru.dashboard');
    return redirect()->route('siswa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'totalStudents' => \App\Models\User::where('role', 'siswa')->count(),
            'totalTeachers' => \App\Models\User::where('role', 'guru')->count(),
            'totalClasses' => \App\Models\SchoolClass::count(),
            'recentClasses' => \App\Models\SchoolClass::latest()->take(5)->get()
        ]);
    })->name('dashboard');

    Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
    Route::put('/classes/{id}', [SchoolClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{id}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::patch('/students/{user}/assign-class', [StudentController::class, 'updateClass'])->name('students.update-class');

    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
});

Route::middleware(['auth', 'verified', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/assignments/{id}', [\App\Http\Controllers\AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments', [\App\Http\Controllers\AssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('/assignments/{id}', [\App\Http\Controllers\AssignmentController::class, 'destroy'])->name('assignments.destroy');
    
    Route::post('/submissions/{id}/grade', [\App\Http\Controllers\SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::get('/submissions/{id}/download', [\App\Http\Controllers\SubmissionController::class, 'download'])->name('submissions.download');

    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [\App\Http\Controllers\AttendanceController::class, 'store'])->name('attendance.store');
});

Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\StudentDashboardController::class, 'index'])->name('dashboard');
    Route::post('/submissions', [\App\Http\Controllers\SubmissionController::class, 'store'])->name('submissions.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
