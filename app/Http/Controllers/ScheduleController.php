<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Schedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Inertia\Inertia;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('grade_level')->orderBy('name')->get();
        $classId = $request->query('class_id');
        
        if (!$classId && $classes->isNotEmpty()) {
            $classId = $classes->first()->id;
        }

        $schedules = [];

        if ($classId) {
            $schedules = Schedule::with(['schoolClass', 'subject', 'teacher'])
                ->where('school_class_id', $classId)
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

        return Inertia::render('Admin/Schedules/Index', [
            'schedules' => $schedules,
            'classes' => $classes,
            'subjects' => Subject::orderBy('name')->get(),
            'teachers' => User::where('role', 'guru')->orderBy('name')->get(),
            'selectedClass' => $classId
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'nullable|exists:users,id',
            'day' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
        ]);

        Schedule::create($validated);

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil ditambahkan.');
    }

    public function destroy(int $id)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect()->back()->with('success', 'Jadwal pelajaran berhasil dihapus.');
    }
}
