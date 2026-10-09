<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        $selectedClassId = $request->query('school_class_id');
        $selectedSubjectId = $request->query('subject_id');
        $month = $request->query('month', Carbon::today()->format('Y-m'));

        $attendances = [];
        $students = [];
        $report = [];

        if ($selectedClassId) {
            $students = User::where('role', 'siswa')
                            ->where('class_id', $selectedClassId)
                            ->orderBy('name', 'asc')
                            ->get();

            $query = Attendance::where('school_class_id', $selectedClassId)
                ->where('date', 'like', $month . '%');
                
            if ($selectedSubjectId) {
                $query->where('subject_id', $selectedSubjectId);
            }

            $attendances = $query->get();
            
            foreach ($students as $student) {
                $studentAtts = $attendances->where('student_id', $student->id);
                $report[] = [
                    'student' => $student,
                    'hadir' => $studentAtts->where('status', 'hadir')->count(),
                    'sakit' => $studentAtts->where('status', 'sakit')->count(),
                    'izin' => $studentAtts->where('status', 'izin')->count(),
                    'alpa' => $studentAtts->where('status', 'alpa')->count(),
                    'total' => $studentAtts->count()
                ];
            }
        }

        return Inertia::render('Admin/Attendance/Index', [
            'classes' => $classes,
            'subjects' => $subjects,
            'report' => $report,
            'filters' => [
                'school_class_id' => $selectedClassId,
                'subject_id' => $selectedSubjectId,
                'month' => $month,
            ]
        ]);
    }
}
