<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Schedule;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = auth()->id();
        
        $schedules = Schedule::where('teacher_id', $teacherId)->with(['schoolClass', 'subject'])->get();
        $classes = $schedules->pluck('schoolClass')->unique('id')->values();
        $subjects = $schedules->pluck('subject')->unique('id')->values();

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

        return Inertia::render('Guru/Attendance/Index', [
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

    public function create(Request $request)
    {
        $teacherId = auth()->id();
        
        // Get unique classes and subjects the teacher teaches
        $schedules = Schedule::where('teacher_id', $teacherId)->with(['schoolClass', 'subject'])->get();
        $classes = $schedules->pluck('schoolClass')->unique('id')->values();
        $subjects = $schedules->pluck('subject')->unique('id')->values();

        $selectedClassId = $request->query('school_class_id');
        $selectedSubjectId = $request->query('subject_id');
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));

        $students = [];
        $existingAttendances = [];

        if ($selectedClassId) {
            $students = User::where('role', 'siswa')
                            ->where('class_id', $selectedClassId)
                            ->orderBy('name', 'asc')
                            ->get();

            if ($selectedSubjectId) {
                $existingAttendances = Attendance::where('school_class_id', $selectedClassId)
                    ->where('subject_id', $selectedSubjectId)
                    ->where('date', $selectedDate)
                    ->get()
                    ->keyBy('student_id');
            }
        }

        return Inertia::render('Guru/Attendance/Create', [
            'classes' => $classes,
            'subjects' => $subjects,
            'students' => $students,
            'existingAttendances' => $existingAttendances,
            'filters' => [
                'school_class_id' => $selectedClassId,
                'subject_id' => $selectedSubjectId,
                'date' => $selectedDate,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:users,id',
            'attendances.*.status' => 'required|in:hadir,sakit,izin,alpa',
            'attendances.*.note' => 'nullable|string',
        ]);

        $teacherId = auth()->id();

        foreach ($request->attendances as $att) {
            Attendance::updateOrCreate(
                [
                    'school_class_id' => $request->school_class_id,
                    'student_id' => $att['student_id'],
                    'date' => $request->date,
                    'subject_id' => $request->subject_id,
                ],
                [
                    'teacher_id' => $teacherId,
                    'status' => $att['status'],
                    'note' => $att['note'] ?? null,
                ]
            );
        }

        return redirect()->back()->with('success', 'Presensi berhasil disimpan.');
    }
}
