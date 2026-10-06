<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TeacherMaterialController extends Controller
{
    public function index(Request $request)
    {
        $teacherId = $request->user()->id;
        
        $materials = Material::with(['schoolClass', 'subject'])
            ->where('teacher_id', $teacherId)
            ->latest()
            ->get();
            
        $classes = SchoolClass::whereIn('id', function($query) use ($teacherId) {
            $query->select('school_class_id')
                ->from('schedules')
                ->where('teacher_id', $teacherId);
        })->orderBy('grade_level')->orderBy('name')->get();

        // Optional: only fetch subjects they teach, but we can stick to their subject_id
        $subjects = Subject::where('id', $request->user()->subject_id)->get();

        return Inertia::render('Guru/Materials/Index', [
            'materials' => $materials,
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'school_class_id' => 'required|exists:school_classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,ppt,pptx,doc,docx,zip|max:10240',
            'link_url' => 'nullable|url|max:2083',
        ]);

        if (!$request->hasFile('file') && empty($validated['link_url'])) {
            return back()->withErrors(['file' => 'Minimal harus menyertakan berkas (File) atau Tautan (URL).'])->withInput();
        }

        $material = new Material();
        $material->teacher_id = $request->user()->id;
        $material->school_class_id = $validated['school_class_id'];
        $material->subject_id = $validated['subject_id'];
        $material->title = $validated['title'];
        $material->description = $validated['description'] ?? null;
        $material->link_url = $validated['link_url'] ?? null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('materials', 'public');
            $material->file_path = $path;
            $material->file_name = $file->getClientOriginalName();
        }

        $material->save();

        return redirect()->back()->with('success', 'Materi berhasil diunggah.');
    }

    public function destroy(Material $material)
    {
        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
        $material->delete();

        return redirect()->back()->with('success', 'Materi berhasil dihapus.');
    }
}
