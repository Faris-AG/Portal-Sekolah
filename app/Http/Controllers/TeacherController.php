<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = $request->query('sort_by', 'name');
        $direction = $request->query('direction', 'asc');
        $perPage = $request->query('per_page', 10);

        $allowedSorts = ['name', 'email', 'subject_id'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query = User::with(['subject', 'waliClass'])
            ->where('role', 'guru');
            
        if ($sortBy === 'subject_id') {
            $query->orderBy('subject_id', $direction);
        } else {
            $query->orderBy($sortBy, $direction);
        }

        $teachers = $query->paginate($perPage)->withQueryString();
            
        $subjects = Subject::all();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'subjects' => $subjects,
            'filters' => [
                'sort_by' => $sortBy,
                'direction' => $direction,
                'per_page' => $perPage,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'guru',
            'subject_id' => $request->subject_id,
        ]);

        return redirect()->back()->with('success', 'Akun Guru berhasil ditambahkan.');
    }

    public function update(Request $request, User $teacher)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$teacher->id,
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $teacher->update([
            'name' => $request->name,
            'email' => $request->email,
            'subject_id' => $request->subject_id,
        ]);
        
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $teacher->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->back()->with('success', 'Akun Guru berhasil diperbarui.');
    }

    public function destroy(User $teacher)
    {
        if ($teacher->role !== 'guru') {
            abort(403);
        }
        $teacher->delete();
        return redirect()->back()->with('success', 'Akun Guru berhasil dihapus.');
    }
}
