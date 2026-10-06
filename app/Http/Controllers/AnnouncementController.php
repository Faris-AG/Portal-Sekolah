<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('user')->latest()->get();
        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_role' => 'required|in:all,guru,siswa',
        ]);

        $request->user()->announcements()->create($validated);

        return redirect()->back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_role' => 'required|in:all,guru,siswa',
        ]);

        $announcement->update($validated);

        return redirect()->back()->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->back()->with('success', 'Pengumuman berhasil dihapus.');
    }
}
