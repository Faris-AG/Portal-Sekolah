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

    public function show(Request $request, $id)
    {
        $sortBy = $request->query('sort_by', 'student_name');
        $direction = $request->query('direction', 'asc');
        $perPage = $request->query('per_page', 25);

        $allowedSorts = ['student_name', 'grade', 'submitted_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'student_name';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $assignment = Assignment::with([
            'schoolClass',
            'subject',
        ])->where('teacher_id', auth()->id())->findOrFail($id);
        
        // Paginate all students in the class, left join with their submissions
        $studentsQuery = \App\Models\User::where('role', 'siswa')
            ->where('class_id', $assignment->school_class_id)
            ->leftJoin('submissions', function($join) use ($id) {
                $join->on('users.id', '=', 'submissions.student_id')
                     ->where('submissions.assignment_id', '=', $id);
            })
            ->select('users.id as student_id', 'users.name as student_name', 'users.email as student_email', 'submissions.id as submission_id', 'submissions.grade', 'submissions.submitted_at', 'submissions.file_path', 'submissions.file_name', 'submissions.note', 'submissions.feedback');
            
        if ($sortBy === 'student_name') {
            $studentsQuery->orderBy('users.name', $direction);
        } else if ($sortBy === 'grade') {
            $studentsQuery->orderBy('submissions.grade', $direction);
        } else if ($sortBy === 'submitted_at') {
            $studentsQuery->orderBy('submissions.submitted_at', $direction);
        }

        $students = $studentsQuery->paginate($perPage)->withQueryString();

        return \Inertia\Inertia::render('Guru/Assignments/Show', [
            'assignment' => $assignment,
            'studentsList' => $students,
            'filters' => [
                'sort_by' => $sortBy,
                'direction' => $direction,
                'per_page' => $perPage,
            ]
        ]);
    }

    public function destroy($id)
    {
        $assignment = Assignment::where('teacher_id', auth()->id())->findOrFail($id);
        $assignment->delete();

        return redirect()->back()->with('success', 'Tugas berhasil dihapus.');
    }

    public function exportCsv($id)
    {
        $assignment = Assignment::with(['schoolClass'])->where('teacher_id', auth()->id())->findOrFail($id);
        
        $submissions = \App\Models\Submission::where('assignment_id', $id)
            ->join('users', 'submissions.student_id', '=', 'users.id')
            ->select('submissions.*', 'users.name as student_name', 'users.email as student_email')
            ->orderBy('users.name', 'asc')
            ->get();
            
        $submittedStudentIds = $submissions->pluck('student_id')->toArray();
        $unsubmittedStudents = \App\Models\User::where('role', 'siswa')
            ->where('class_id', $assignment->school_class_id)
            ->whereNotIn('id', $submittedStudentIds)
            ->orderBy('name', 'asc')
            ->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=Rekap_Nilai_{$assignment->title}.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($submissions, $unsubmittedStudents, $assignment) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for UTF-8 compatibility in Excel
            fputs($file, "\xEF\xBB\xBF");
            
            fputcsv($file, ['Nama Siswa', 'Kelas', 'Status Pengumpulan', 'Tanggal Kumpul', 'Nilai']);

            foreach ($submissions as $sub) {
                fputcsv($file, [
                    $sub->student_name,
                    $assignment->schoolClass->name,
                    'Sudah Mengumpulkan',
                    $sub->submitted_at ? \Carbon\Carbon::parse($sub->submitted_at)->format('Y-m-d H:i') : '-',
                    $sub->grade !== null ? $sub->grade : 'Belum Dinilai'
                ]);
            }
            
            foreach ($unsubmittedStudents as $student) {
                fputcsv($file, [
                    $student->name,
                    $assignment->schoolClass->name,
                    'Belum Mengumpulkan',
                    '-',
                    '0'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
