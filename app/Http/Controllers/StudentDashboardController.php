<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Carbon;
use App\Models\Schedule;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $user->load('schoolClass');
        
        $todaySchedules = [];
        $allSchedules = [];

        if ($user->class_id) {
            $dayMap = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $todayNameEnglish = Carbon::now()->format('l');
            $todayName = $dayMap[$todayNameEnglish] ?? 'Senin';

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

            $todaySchedules = $allSchedules->filter(function ($schedule) use ($todayName) {
                return $schedule->day === $todayName;
            })->values();
        $tugasTerdekat = [];
        $attendanceStats = ['hadir' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];
        $recentAttendances = [];

        if ($user->class_id) {
            $tugasTerdekat = \App\Models\Assignment::with(['subject', 'teacher', 'submissions' => function($q) use ($user) {
                $q->where('student_id', $user->id);
            }])
            ->where('school_class_id', $user->class_id)
            ->orderBy('due_date', 'asc')
            ->get();

            // Attendance Data
            $attendances = \App\Models\Attendance::where('student_id', $user->id)->get();
            $attendanceStats = [
                'hadir' => $attendances->where('status', 'hadir')->count(),
                'sakit' => $attendances->where('status', 'sakit')->count(),
                'izin' => $attendances->where('status', 'izin')->count(),
                'alpa' => $attendances->where('status', 'alpa')->count(),
            ];

            $recentAttendances = \App\Models\Attendance::with(['subject'])
                ->where('student_id', $user->id)
                ->orderBy('date', 'desc')
                ->take(5)
                ->get();
        }

        return Inertia::render('Siswa/Dashboard', [
            'student' => $user,
            'schoolClass' => $user->schoolClass,
            'todaySchedules' => $todaySchedules,
            'allSchedules' => $allSchedules,
            'tugasTerdekat' => $tugasTerdekat,
            'attendanceStats' => $attendanceStats,
            'recentAttendances' => $recentAttendances,
        ]);
    }
}
}