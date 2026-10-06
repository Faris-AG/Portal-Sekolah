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

        $announcements = \App\Models\Announcement::whereIn('target_role', ['all', 'siswa'])
            ->latest()
            ->take(3)
            ->get();

        return Inertia::render('Siswa/Dashboard', [
            'student' => $user,
            'schoolClass' => $user->schoolClass,
            'todaySchedules' => $todaySchedules,
            'tugasTerdekat' => $tugasTerdekat,
            'attendanceStats' => $attendanceStats,
            'announcements' => $announcements,
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
    
    public function showAssignment(Request $request, $id)
    {
        $user = $request->user();
        
        $assignment = \App\Models\Assignment::with(['subject', 'teacher', 'submissions' => function($q) use ($user) {
            $q->where('student_id', $user->id);
        }])->findOrFail($id);
        
        if ($assignment->school_class_id !== $user->class_id) {
            abort(403);
        }

        return Inertia::render('Siswa/Assignments/Show', [
            'assignment' => $assignment,
        ]);
    }

    public function grades(Request $request)
    {
        $user = $request->user();
        
        $submissions = \App\Models\Submission::with(['assignment.subject'])
            ->where('student_id', $user->id)
            ->whereNotNull('grade')
            ->get();
            
        // Hitung ringkasan statistik
        $totalGraded = $submissions->count();
        $averageGrade = $totalGraded > 0 ? round($submissions->avg('grade'), 1) : 0;
        $highestGrade = $totalGraded > 0 ? $submissions->max('grade') : 0;
        
        // Kelompokkan riwayat nilai berdasarkan Mata Pelajaran
        $gradesBySubject = [];
        foreach($submissions as $sub) {
            $subjectName = $sub->assignment->subject->name ?? 'Lainnya';
            if (!isset($gradesBySubject[$subjectName])) {
                $gradesBySubject[$subjectName] = [];
            }
            
            $gradesBySubject[$subjectName][] = [
                'assignment_title' => $sub->assignment->title,
                'graded_at' => $sub->updated_at,
                'grade' => $sub->grade,
                'feedback' => $sub->feedback,
            ];
        }
        
        // Sort each subject's grades by date descending
        foreach($gradesBySubject as $key => $subjectGrades) {
            usort($gradesBySubject[$key], function($a, $b) {
                return $b['graded_at'] <=> $a['graded_at'];
            });
        }

        return Inertia::render('Siswa/Grades/Index', [
            'gradesBySubject' => $gradesBySubject,
            'statistics' => [
                'average' => $averageGrade,
                'total' => $totalGraded,
                'highest' => $highestGrade,
            ],
        ]);
    }
}