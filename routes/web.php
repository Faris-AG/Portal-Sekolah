<?php

use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\ForumModerationController;
use App\Http\Controllers\GlobalAnnouncementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentMaterialController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeacherDashboardController;
use App\Http\Controllers\TeacherMaterialController;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
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

Route::get('/dashboard', function (Request $request) {
    /** @var User $user */
    $user = $request->user();
    if ($user->isAdmin()) return redirect()->route('admin.dashboard');
    if ($user->isGuru()) return redirect()->route('guru.dashboard');
    return redirect()->route('siswa.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'totalStudents' => User::where('role', 'siswa')->verified()->count(),
            'totalTeachers' => User::where('role', 'guru')->verified()->count(),
            'totalClasses'  => SchoolClass::count(),
            'recentClasses' => SchoolClass::latest()->take(5)->get(),
        ]);
    })->name('dashboard');

    Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{id}', [SchoolClassController::class, 'show'])->name('classes.show');
    Route::put('/classes/{id}', [SchoolClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{id}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');

    Route::resource('teachers', TeacherController::class)->except(['create', 'edit', 'show'])->names('teachers');

    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{id}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])->name('subjects.destroy');

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students/bulk-update-class', [StudentController::class, 'bulkUpdateClass'])->name('students.bulk-update-class');
    Route::patch('/students/{user}/assign-class', [StudentController::class, 'updateClass'])->name('students.update-class');


    Route::get('/schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{id}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::resource('announcements', AnnouncementController::class)->except(['create', 'edit', 'show'])->names('announcements');
    
    Route::get('/attendance', [AdminAttendanceController::class, 'index'])->name('attendance.index');
    
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/{id}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/forum', [ForumController::class, 'storeTopic'])->name('forum.store');
    Route::post('/forum/{id}/reply', [ForumController::class, 'storeReply'])->name('forum.reply');
    Route::delete('/forum/{id}', [ForumController::class, 'destroyTopic'])->name('forum.topic.destroy');
    Route::patch('/forum/{id}/toggle-pin', [ForumController::class, 'togglePin'])->name('forum.topic.toggle-pin');
    Route::patch('/forum/{id}/toggle-lock', [ForumController::class, 'toggleLock'])->name('forum.topic.toggle-lock');
    // Moderasi
    Route::delete('/forum/reply/{replyId}', [ForumModerationController::class, 'destroyReply'])->name('forum.reply.destroy');
    Route::patch('/forum/reply/{replyId}', [ForumModerationController::class, 'updateReply'])->name('forum.reply.update');
    Route::get('/forum/reports/list', [ForumModerationController::class, 'reportedReplies'])->name('forum.reports');
    Route::match(['put', 'patch', 'delete'], '/forum/reports/{reportId}/status', [ForumModerationController::class, 'updateReportStatus'])->name('forum.reports.status');
    Route::delete('/forum/reports/{reportId}', [ForumModerationController::class, 'destroyReport'])->name('forum.reports.destroy');
});

Route::middleware(['auth', 'verified', 'role:guru'])->prefix('guru')->name('guru.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{id}', [AssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments', [AssignmentController::class, 'store'])->name('assignments.store');
    Route::delete('/assignments/{id}', [AssignmentController::class, 'destroy'])->name('assignments.destroy');
    Route::get('/assignments/{id}/export', [AssignmentController::class, 'exportCsv'])->name('assignments.export');
    
    Route::get('/materials', [TeacherMaterialController::class, 'index'])->name('materials.index');
    Route::post('/materials', [TeacherMaterialController::class, 'store'])->name('materials.store');
    Route::delete('/materials/{material}', [TeacherMaterialController::class, 'destroy'])->name('materials.destroy');
    
    Route::post('/submissions/{id}/grade', [SubmissionController::class, 'grade'])->name('submissions.grade');
    Route::get('/submissions/{id}/download', [SubmissionController::class, 'download'])->name('submissions.download');

    Route::get('/attendance/report', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/{id}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/forum', [ForumController::class, 'storeTopic'])->name('forum.store');
    Route::post('/forum/{id}/reply', [ForumController::class, 'storeReply'])->name('forum.reply');
    Route::delete('/forum/{id}', [ForumController::class, 'destroyTopic'])->name('forum.topic.destroy');
    Route::patch('/forum/{id}/toggle-pin', [ForumController::class, 'togglePin'])->name('forum.topic.toggle-pin');
    Route::patch('/forum/{id}/toggle-lock', [ForumController::class, 'toggleLock'])->name('forum.topic.toggle-lock');
    // Moderasi
    Route::delete('/forum/reply/{replyId}', [ForumModerationController::class, 'destroyReply'])->name('forum.reply.destroy');
    Route::patch('/forum/reply/{replyId}', [ForumModerationController::class, 'updateReply'])->name('forum.reply.update');
    Route::get('/forum/reports/list', [ForumModerationController::class, 'reportedReplies'])->name('forum.reports');
    Route::match(['put', 'patch', 'delete'], '/forum/reports/{reportId}/status', [ForumModerationController::class, 'updateReportStatus'])->name('forum.reports.status');
    Route::delete('/forum/reports/{reportId}', [ForumModerationController::class, 'destroyReport'])->name('forum.reports.destroy');
});

Route::middleware(['auth', 'verified', 'role:siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
    Route::get('/schedules', [StudentDashboardController::class, 'schedules'])->name('schedules');
    Route::get('/assignments', [StudentDashboardController::class, 'assignments'])->name('assignments');
    Route::get('/assignments/{id}', [StudentDashboardController::class, 'showAssignment'])->name('assignments.show');
    Route::get('/grades', [StudentDashboardController::class, 'grades'])->name('grades');
    Route::get('/attendance', [StudentDashboardController::class, 'attendance'])->name('attendance');
    Route::post('/submissions', [SubmissionController::class, 'store'])->name('submissions.store');
    Route::get('/materials', [StudentMaterialController::class, 'index'])->name('materials.index');
    
    Route::get('/forum', [ForumController::class, 'index'])->name('forum.index');
    Route::get('/forum/{id}', [ForumController::class, 'show'])->name('forum.show');
    Route::post('/forum', [ForumController::class, 'storeTopic'])->name('forum.store');
    Route::post('/forum/{id}/reply', [ForumController::class, 'storeReply'])->name('forum.reply');
    Route::delete('/forum/{id}', [ForumController::class, 'destroyTopic'])->name('forum.topic.destroy');
    // Moderasi — siswa hanya bisa hapus & laporkan
    Route::delete('/forum/reply/{replyId}', [ForumModerationController::class, 'destroyReply'])->name('forum.reply.destroy');
    Route::patch('/forum/reply/{replyId}', [ForumModerationController::class, 'updateReply'])->name('forum.reply.update');
    Route::post('/forum/reply/{replyId}/report', [ForumModerationController::class, 'reportReply'])->name('forum.reply.report');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    Route::get('/announcements', [GlobalAnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('/announcements/mark-all-read', [GlobalAnnouncementController::class, 'markAllAsRead'])->name('announcements.mark_all_read');
    Route::post('/announcements/{id}/read', [GlobalAnnouncementController::class, 'markAsRead'])->name('announcements.mark_read');
});

require __DIR__.'/auth.php';
