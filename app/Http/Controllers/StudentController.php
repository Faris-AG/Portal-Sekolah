<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use App\Models\SchoolClass;
use Inertia\Inertia;

class StudentController extends Controller
{
    public function index()
    {
        $students = User::where('role', 'siswa')->with('schoolClass')->orderBy('name')->get();
        $classes = SchoolClass::orderBy('grade_level')->orderBy('name')->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'classes' => $classes,
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
