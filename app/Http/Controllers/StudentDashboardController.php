<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Carbon;
use App\Models\Schedule;

class StudentDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $user->load('schoolClass');
        
        $todaySchedules = [];
        $tugasTerdekat = [];
        $attendanceStats = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

        if ($user->class_id) {
            $dayMap = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
            ];
            $todayName = $dayMap[Carbon::now()->format('l')] ?? 'Senin';

            $todaySchedules = Schedule::with(['subject', 'teacher'])
                ->where('school_class_id', $user->class_id)
                ->where('day', $todayName)
                ->orderBy('start_time')
                ->get();

            $tugasTerdekat = \App\Models\Assignment::with(['subject', 'teacher', 'submissions' => function($q) use ($user) {
                $q->where('student_id', $user->id);
            }])
            ->where('school_class_id', $user->class_id)
            ->whereDate('due_date', '>=', Carbon::now())
            ->orderBy('due_date', 'asc')
            ->take(3)
            ->get();

            $attendances = \App\Models\Attendance::where('student_id', $user->id)->get();
            $attendanceStats = [
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'alpa' => $attendances->where('status', 'alpa')->count(),
            ];
        }

        return Inertia::render('Siswa/Dashboard', [
            'student' => $user,
            'schoolClass' => $user->schoolClass,
            'todaySchedules' => $todaySchedules,
            'tugasTerdekat' => $tugasTerdekat,
            'attendanceStats' => $attendanceStats,
        ]);
    }

    public function schedules(Request $request)
    {
        $user = $request->user();
        $user->load('schoolClass');
        
        $allSchedules = [];

        if ($user->class_id) {
            $allSchedules = Schedule::with(['subject', 'teacher'])
                ->where('school_class_id', $user->class_id)
                ->orderByRaw("CASE day 
                    WHEN 'Senin' THEN 1 
                    WHEN 'Selasa' THEN 2 
                    WHEN 'Rabu' THEN 3 
                    WHEN 'Kamis' THEN 4 
                    WHEN 'Jumat' THEN 5 
                    ELSE 6 END")
                ->orderBy('start_time')
                ->get();
        }

        return Inertia::render('Siswa/Schedules/Index', [
            'student' => $user,
            'schoolClass' => $user->schoolClass,
            'allSchedules' => $allSchedules,
        ]);
    }

    public function assignments(Request $request)
    {
        $user = $request->user();
        $user->load('schoolClass');
        
        $tugas = [];

        if ($user->class_id) {
            $tugas = \App\Models\Assignment::with(['subject', 'teacher', 'submissions' => function($q) use ($user) {
                $q->where('student_id', $user->id);
            }])
            ->where('school_class_id', $user->class_id)
            ->orderBy('due_date', 'desc')
            ->get();
        }

        return Inertia::render('Siswa/Assignments/Index', [
            'tugas' => $tugas,
        ]);
    }
}