<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:assignments,id',
            'file' => 'required|file|mimes:pdf,docx,zip,png,jpg|max:5120',
            'note' => 'nullable|string',
        ]);

        $file = $request->file('file');
        $path = Storage::disk('public')->put('submissions', $file);
        $fileName = $file->getClientOriginalName();

        Submission::updateOrCreate(
            [
                'assignment_id' => $request->assignment_id,
                'student_id' => auth()->id(),
            ],
            [
                'file_path' => $path,
                'file_name' => $fileName,
                'note' => $request->note,
                'submitted_at' => now(),
            ]
        );

        return redirect()->back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function grade(Request $request, $id)
    {
        $request->validate([
            'grade' => 'required|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $submission = Submission::whereHas('assignment', function($q) {
            $q->where('teacher_id', auth()->id());
        })->findOrFail($id);

        $submission->update([
            'grade' => $request->grade,
            'feedback' => $request->feedback,
        ]);

        return redirect()->back()->with('success', 'Nilai berhasil disimpan.');
    }

    public function download($id)
    {
        $submission = Submission::whereHas('assignment', function($q) {
            $q->where('teacher_id', auth()->id());
        })->findOrFail($id);

        return Storage::disk('public')->download($submission->file_path, $submission->file_name);
    }
}
