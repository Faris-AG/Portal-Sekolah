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
        
        $materials = Material::with(['subject', 'teacher'])
            ->where('school_class_id', $user->class_id)
            ->latest()
            ->get();
            
        // Group materials by subject for the filter tabs
        $subjects = $materials->map(function ($material) {
            return $material->subject;
        })->unique('id')->values();

        return Inertia::render('Siswa/Materials/Index', [
            'materials' => $materials,
            'subjects' => $subjects,
        ]);
    }
}
