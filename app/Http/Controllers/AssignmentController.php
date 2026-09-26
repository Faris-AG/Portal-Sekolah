<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;

class AssignmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'due_date' => 'required|date',
            'description' => 'required|string',
        ]);

        $validated['teacher_id'] = auth()->id();

        Assignment::create($validated);

        return redirect()->back()->with('success', 'Tugas berhasil dibuat.');
    }

    public function show($id)
    {
        $assignment = Assignment::with([
            'schoolClass.students' => function($q) {
                $q->where('role', 'siswa');
            }, 
            'subject',
            'submissions.student'
        ])->where('teacher_id', auth()->id())->findOrFail($id);

        return \Inertia\Inertia::render('Guru/Assignments/Show', [
            'assignment' => $assignment
        ]);
    }

    public function destroy($id)
    {
        $assignment = Assignment::where('teacher_id', auth()->id())->findOrFail($id);
        $assignment->delete();

        return redirect()->back()->with('success', 'Tugas berhasil dihapus.');
    }
}
