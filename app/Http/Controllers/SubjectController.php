<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Subject;
use Inertia\Inertia;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::orderBy('code')->get();
        
        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
        ]);

        Subject::create($validated);

        return redirect()->back()->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('subjects')->ignore($subject->id)],
            'description' => 'nullable|string',
        ]);

        $subject->update($validated);

        return redirect()->back()->with('success', 'Mata Pelajaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return redirect()->back()->with('success', 'Mata Pelajaran berhasil dihapus.');
    }
}
