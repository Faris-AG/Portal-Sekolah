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
    public function index()
    {
        $teachers = User::with(['subject', 'waliClass'])
            ->where('role', 'guru')
            ->latest()
            ->get();
            
        $subjects = Subject::all();

        return Inertia::render('Admin/Teachers/Index', [
            'teachers' => $teachers,
            'subjects' => $subjects,
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
