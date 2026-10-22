<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SchoolClass;
use App\Models\ForumTopic;
use App\Models\ForumReply;
use App\Models\ReportedReply;
use App\Services\ProfanityFilterService;
use Illuminate\Support\Facades\DB;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $classId = null;
        $classes = [];

        if ($user->role === 'siswa') {
            $classId = $user->class_id;
        } else if ($user->role === 'guru') {
            // Get classes the teacher teaches
            $schedules = \App\Models\Schedule::where('teacher_id', $user->id)->with('schoolClass')->get();
            $classes = $schedules->pluck('schoolClass')->unique('id')->values();
            $classId = $request->query('school_class_id', $classes->first()?->id);
        } else {
            // Admin can see all
            $classes = SchoolClass::orderBy('name')->get();
            $classId = $request->query('school_class_id', $classes->first()?->id);
        }

        $topics = [];
        $currentClass = null;

        if ($classId) {
            $currentClass = SchoolClass::find($classId);
            $topics = ForumTopic::with(['user', 'replies'])
                ->withCount('replies')
                ->where('school_class_id', $classId)
                ->orderByDesc('is_pinned')
                ->latest()
                ->get();
        }

        return Inertia::render('Forum/Index', [
            'topics' => $topics,
            'classes' => $classes,
            'currentClass' => $currentClass,
            'filters' => [
                'school_class_id' => $classId
            ]
        ]);
    }

    public function show(Request $request, int|string $id)
    {
        $topic = ForumTopic::with(['user', 'schoolClass', 'replies.user', 'replies.moderator'])->findOrFail($id);
        
        $user = $request->user();
        if ($user->role === 'siswa' && $topic->school_class_id !== $user->class_id) {
            abort(403);
        }

        return Inertia::render('Forum/Show', [
            'topic'      => $topic,
            'authUserId' => $user->id,
            'authRole'   => $user->role,
        ]);
    }

    public function storeTopic(Request $request)
    {
        $request->validate([
            'school_class_id' => 'required|exists:school_classes,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $user = $request->user();
        if ($user->role === 'siswa' && $request->school_class_id != $user->class_id) {
            abort(403);
        }

        // Sensor kata kasar sebelum disimpan ke database
        $filter  = new ProfanityFilterService();
        $rawTitle   = $request->input('title');
        $rawContent = $request->input('content');

        $filteredTitle   = $filter->filter($rawTitle);
        $filteredContent = $filter->filter($rawContent);

        $wasFiltered = ($filteredTitle !== $rawTitle || $filteredContent !== $rawContent);

        ForumTopic::create([
            'school_class_id' => $request->input('school_class_id'),
            'user_id'         => $user->id,
            'title'           => $filteredTitle,
            'content'         => $filteredContent,
        ]);

        $flashData = ['success' => 'Topik diskusi berhasil dibuat.'];
        if ($wasFiltered) {
            $flashData['profanity_detected'] = 'Beberapa kata dalam postingan Anda telah disensor secara otomatis sesuai aturan komunitas.';
        }

        return back()->with($flashData);
    }

    public function storeReply(Request $request, int|string $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $topic = ForumTopic::findOrFail($id);
        $user = $request->user();

        if ($user->role === 'siswa' && $topic->school_class_id !== $user->class_id) {
            abort(403);
        }

        // Cegah balasan jika topik dikunci
        if ($topic->is_locked) {
            abort(403, 'Topik ini telah dikunci. Anda tidak dapat menambahkan balasan baru.');
        }

        // Sensor kata kasar sebelum disimpan ke database
        $filter     = new ProfanityFilterService();
        $rawContent = $request->input('content');
        $filteredContent = $filter->filter($rawContent);
        $wasFiltered     = ($filteredContent !== $rawContent);

        ForumReply::create([
            'forum_topic_id' => $topic->id,
            'user_id'        => $user->id,
            'content'        => $filteredContent,
        ]);

        // Update topic updated_at so it bumps to the top
        $topic->touch();

        $flashData = ['success' => 'Balasan berhasil dikirim.'];
        if ($wasFiltered) {
            $flashData['profanity_detected'] = 'Beberapa kata dalam balasan Anda telah disensor secara otomatis sesuai aturan komunitas.';
        }

        return back()->with($flashData);
    }

    /**
     * Hapus topik beserta seluruh balasan dan laporan terkait (cascade delete manual).
     * Hak akses:
     *   - Siswa  : hanya topik milik sendiri
     *   - Guru   : topik milik sendiri atau milik siswa (tidak bisa hapus milik guru lain / admin)
     *   - Admin  : semua topik
     */
    public function destroyTopic(Request $request, int|string $id)
    {
        $topic = ForumTopic::with('user')->findOrFail($id);
        $user  = $request->user();

        // ── Otorisasi ──────────────────────────────────────────────
        if ($user->role === 'siswa') {
            if ($topic->user_id !== $user->id) {
                abort(403, 'Anda hanya dapat menghapus topik milik Anda sendiri.');
            }
        } elseif ($user->role === 'guru') {
            // Guru tidak bisa hapus topik milik guru lain atau admin
            if ($topic->user_id !== $user->id && in_array($topic->user->role, ['guru', 'admin'])) {
                abort(403, 'Anda tidak dapat menghapus topik milik Guru atau Admin lain.');
            }
        }
        // Admin: tidak ada pembatasan

        // ── Cascade Delete dalam Transaction ───────────────────────
        DB::transaction(function () use ($topic) {
            // 1. Ambil semua ID balasan dalam topik ini
            $replyIds = ForumReply::where('forum_topic_id', $topic->id)->pluck('id');

            // 2. Hapus semua laporan (reported_replies) yang terkait dengan balasan-balasan tersebut
            if ($replyIds->isNotEmpty()) {
                ReportedReply::whereIn('reply_id', $replyIds)->delete();
            }

            // 3. Hapus semua balasan dalam topik ini
            ForumReply::where('forum_topic_id', $topic->id)->delete();

            // 4. Hapus topik utama
            $topic->delete();
        });

        return redirect()
            ->route($user->role . '.forum.index', ['school_class_id' => $topic->school_class_id])
            ->with('success', 'Topik diskusi berhasil dihapus.');
    }

    /**
     * Toggle pin topik — hanya Guru & Admin.
     */
    public function togglePin(Request $request, int|string $id)
    {
        $topic = ForumTopic::findOrFail($id);
        $user  = $request->user();

        if (!in_array($user->role, ['guru', 'admin'])) {
            abort(403);
        }

        $topic->update(['is_pinned' => !$topic->is_pinned]);

        $message = $topic->is_pinned
            ? 'Topik berhasil disematkan.'
            : 'Topik berhasil dilepas dari sematan.';

        return back()->with('success', $message);
    }

    /**
     * Toggle lock topik — hanya Guru & Admin.
     */
    public function toggleLock(Request $request, int|string $id)
    {
        $topic = ForumTopic::findOrFail($id);
        $user  = $request->user();

        if (!in_array($user->role, ['guru', 'admin'])) {
            abort(403);
        }

        $topic->update(['is_locked' => !$topic->is_locked]);

        $message = $topic->is_locked
            ? 'Topik berhasil dikunci.'
            : 'Topik berhasil dibuka kembali.';

        return back()->with('success', $message);
    }
}
