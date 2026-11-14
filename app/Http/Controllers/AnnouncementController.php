<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    /**
     * Menampilkan daftar pengumuman dengan filter, eager loading, reader count, dan paginasi.
     */
    public function index(Request $request)
    {
        $query = Announcement::query()
            ->with('user:id,name')
            ->withCount(['readByUsers as readers_count' => function ($q) {
                $q->whereNotNull('announcement_user.read_at');
            }]);

        // Filter pencarian berdasarkan judul atau isi pengumuman
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter sasaran target pengumuman (all, guru, siswa)
        if ($request->filled('target_role') && in_array($request->input('target_role'), ['all', 'guru', 'siswa'])) {
            $query->where('target_role', $request->input('target_role'));
        }

        // Filter rentang tanggal terbit
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }

        $announcements = $query->latest()->paginate($perPage)->withQueryString();

        return Inertia::render('Admin/Announcements/Index', [
            'announcements' => $announcements,
            'filters' => [
                'search'      => $request->input('search', ''),
                'target_role' => $request->input('target_role', ''),
                'start_date'  => $request->input('start_date', ''),
                'end_date'    => $request->input('end_date', ''),
                'per_page'    => $perPage,
            ],
        ]);
    }

    /**
     * Mode 1: Cetak / Export Rekapitulasi Pengumuman (A4 View)
     */
    public function printRekap(Request $request)
    {
        $query = Announcement::query()
            ->with('user:id,name')
            ->withCount(['readByUsers as readers_count' => function ($q) {
                $q->whereNotNull('announcement_user.read_at');
            }]);

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('target_role') && in_array($request->input('target_role'), ['all', 'guru', 'siswa'])) {
            $query->where('target_role', $request->input('target_role'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $announcements = $query->latest()->get();

        $filters = [
            'search'      => $request->input('search', ''),
            'target_role' => $request->input('target_role', ''),
            'start_date'  => $request->input('start_date', ''),
            'end_date'    => $request->input('end_date', ''),
        ];

        return view('admin.announcements.rekap-pdf', [
            'announcements' => $announcements,
            'filters'       => $filters,
            'printedBy'     => $request->user()?->name ?? 'Administrator',
            'printedAt'     => now(),
        ]);
    }

    /**
     * Mode 2: Cetak Surat Edaran / Pengumuman Resmi SMKN 1 Ciomas (1 Pengumuman)
     */
    public function printLetter(Request $request, Announcement $announcement)
    {
        $announcement->load('user:id,name');
        $announcement->loadCount(['readByUsers as readers_count' => function ($q) {
            $q->whereNotNull('announcement_user.read_at');
        }]);

        return view('admin.announcements.letter-pdf', [
            'announcement' => $announcement,
            'printedBy'    => $request->user()?->name ?? 'Administrator',
            'printedAt'    => now(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'target_role' => 'required|in:all,guru,siswa',
        ]);

        $request->user()->announcements()->create($validated);

        return redirect()->back()->with('success', 'Pengumuman berhasil dibuat.');
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
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
