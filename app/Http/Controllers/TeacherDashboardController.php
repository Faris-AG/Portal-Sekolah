<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Schedule;
use App\Models\Assignment;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = $request->user()->id;
        
        $schedules = Schedule::where('teacher_id', $teacherId)
            ->with(['schoolClass', 'subject'])
            ->orderByRaw("CASE day 
                WHEN 'Senin' THEN 1 
                WHEN 'Selasa' THEN 2 
                WHEN 'Rabu' THEN 3 
                WHEN 'Kamis' THEN 4 
                WHEN 'Jumat' THEN 5 
                ELSE 6 END")
            ->orderBy('start_time')
            ->get();
            
        $assignments = Assignment::where('teacher_id', $teacherId)
            ->with(['schoolClass', 'subject'])
            ->latest()
            ->get();
            
        $classes = SchoolClass::orderBy('grade_level')->orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();

        return Inertia::render('Guru/Dashboard', [
            'schedules' => $schedules,
            'assignments' => $assignments,
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    }
}
