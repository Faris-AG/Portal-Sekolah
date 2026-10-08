<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentMaterialController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $search = $request->query('search', '');
        $subjectId = $request->query('subject_id', 'all');
        
        $query = Material::with(['subject', 'teacher'])
            ->where('school_class_id', $user->class_id);
            
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('description', 'like', '%' . $search . '%');
            });
        }
        
        if ($subjectId !== 'all' && $subjectId !== '') {
            $query->where('subject_id', $subjectId);
        }
            
        $materials = $query->latest()->get();
            
        // Group materials by subject for the filter tabs
        $subjects = $materials->map(function ($material) {
            return $material->subject;
        })->unique('id')->values();

        return Inertia::render('Siswa/Materials/Index', [
            'materials' => $materials,
            'subjects' => $subjects,
            'filters' => [
                'search' => $search,
                'subject_id' => $subjectId,
            ]
        ]);
    }
}
