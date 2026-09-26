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

    public function update(Request $request, $id)
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

    public function destroy($id)
    {
        $class = SchoolClass::findOrFail($id);
        $class->delete();

        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }
}
