<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SchoolClass;
use Inertia\Inertia;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::orderBy('grade_level')->orderBy('name')->get();
        
        return Inertia::render('Admin/Classes/Index', [
            'classes' => $classes,
        ]);
    }

    public function show(int $id)
    {
        $schoolClass = SchoolClass::findOrFail($id);
        
        $students = \App\Models\User::where('role', 'siswa')
            ->where('class_id', $id)
            ->orderBy('name')
            ->get();
            
        $schedules = \App\Models\Schedule::with(['subject', 'teacher'])
            ->where('school_class_id', $id)
            ->orderByRaw("CASE day 
                WHEN 'Senin' THEN 1 
                WHEN 'Selasa' THEN 2 
                WHEN 'Rabu' THEN 3 
                WHEN 'Kamis' THEN 4 
                WHEN 'Jumat' THEN 5 
                ELSE 6 END")
            ->orderBy('start_time')
            ->get();

        return Inertia::render('Admin/Classes/Show', [
            'schoolClass' => $schoolClass,
            'students' => $students,
            'schedules' => $schedules
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|integer|min:1|max:12',
            'wali_kelas' => 'nullable|string|max:255',
        ]);

        SchoolClass::create($validated);

        return redirect()->back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function update(Request $request, int $id)
    {
        $class = SchoolClass::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'grade_level' => 'required|integer|min:1|max:12',
            'wali_kelas' => 'nullable|string|max:255',
        ]);

        $class->update($validated);

        return redirect()->back()->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(int $id)
    {
        $class = SchoolClass::findOrFail($id);
        $class->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }
}
