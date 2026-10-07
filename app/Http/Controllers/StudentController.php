<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\SchoolClass;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'name');
        $direction = $request->query('direction', 'asc');
        $perPage = $request->query('per_page', 10);

        $search = $request->query('search', '');
        $classFilter = $request->query('class_id', '');

        // Define allowed sort columns to prevent SQL injection
        $allowedSorts = ['name', 'email', 'class_id'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query = User::where('role', 'siswa')->with('schoolClass');
        
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            });
        }
        
        if ($classFilter !== '') {
            if ($classFilter === 'null') {
                $query->whereNull('class_id');
            } else {
                $query->where('class_id', $classFilter);
            }
        }
        
        if ($sortBy === 'class_id') {
            // Sort by relation (simplified by sorting the foreign key)
            $query->orderBy('class_id', $direction);
        } else {
            $query->orderBy($sortBy, $direction);
        }

        $students = $query->paginate($perPage)->withQueryString();
        
        $classes = SchoolClass::orderBy('grade_level')->orderBy('name')->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'sort_by' => $sortBy,
                'direction' => $direction,
                'per_page' => $perPage,
                'search' => $search,
                'class_id' => $classFilter,
            ]
        ]);
    }

    public function updateClass(Request $request, User $user)
    {
        if ($user->role !== 'siswa') {
            return redirect()->back()->withErrors('Hanya role siswa yang dapat diubah kelasnya.');
        }

        $validated = $request->validate([
            'class_id' => 'nullable|exists:school_classes,id',
        ]);

        $user->update(['class_id' => $validated['class_id']]);

        return redirect()->back()->with('success', 'Kelas siswa berhasil diperbarui.');
    }
}
