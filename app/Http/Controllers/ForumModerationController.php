<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\ForumReply;
use App\Models\ForumTopic;
use App\Models\ReportedReply;
use App\Services\ProfanityFilterService;

class ForumModerationController extends Controller
{
    // ─────────────────────────────────────────────
    //  HAPUS BALASAN
    // ─────────────────────────────────────────────

    public function destroyReply(Request $request, int $replyId)
    {
        $reply = ForumReply::with('topic')->findOrFail($replyId);
        $user  = $request->user();

        if ($user->role === 'siswa') {
            // Siswa hanya boleh hapus miliknya sendiri → hard delete
            if ($reply->user_id !== $user->id) {
                abort(403, 'Anda tidak berhak menghapus pesan ini.');
            }
            $reply->delete();

        } elseif ($user->role === 'guru') {
            // Guru boleh hapus pesan siswa di kelasnya (tombstone)
            // atau hapus miliknya sendiri (force delete)
            $topic = $reply->topic;

            // Pastikan topic ada di kelas yang diajar guru ini
            $teachesClass = \App\Models\Schedule::where('teacher_id', $user->id)
                ->where('school_class_id', $topic->school_class_id)
                ->exists();

            if ($reply->user_id === $user->id) {
                // Hapus miliknya sendiri → hard delete
                $reply->delete();
            } elseif ($teachesClass) {
                // Tombstone: konten jadi null, catat moderator
                $reply->update([
                    'content'                 => null,
                    'deleted_by_moderator_id' => $user->id,
                ]);
            } else {
                abort(403, 'Anda tidak berhak menghapus pesan ini.');
            }

        } elseif ($user->role === 'admin') {
            // Admin: hapus apa pun dengan tombstone agar audit trail terjaga
            if ($reply->user_id === $user->id) {
                $reply->delete();
            } else {
                $reply->update([
                    'content'                 => null,
                    'deleted_by_moderator_id' => $user->id,
                ]);
            }
        } else {
            abort(403);
        }

        return back()->with('success', 'Pesan berhasil dihapus.');
    }

    // ─────────────────────────────────────────────
    //  EDIT BALASAN (hanya dalam 3 menit setelah posting)
    // ─────────────────────────────────────────────

    public function updateReply(Request $request, int $replyId)
    {
        $reply = ForumReply::findOrFail($replyId);
        $user  = $request->user();

        // Hanya pemilik yang boleh edit
        if ($reply->user_id !== $user->id) {
            abort(403, 'Anda tidak berhak mengedit pesan ini.');
        }

        // Batas waktu edit: 3 menit setelah pesan dikirim
        $minutesSinceCreated = $reply->created_at->diffInMinutes(now());
        if ($minutesSinceCreated > 3) {
            abort(403, 'Waktu edit sudah habis. Pesan hanya bisa diedit dalam 3 menit setelah dikirim.');
        }

        $request->validate([
            'content' => 'required|string|min:1',
        ]);

        // Sensor kata kasar
        $filter          = new ProfanityFilterService();
        $filteredContent = $filter->filter($request->content);
        $wasFiltered     = ($filteredContent !== $request->content);

        $reply->update([
            'content'   => $filteredContent,
            'is_edited' => true,
        ]);

        $flashData = ['success' => 'Pesan berhasil diperbarui.'];
        if ($wasFiltered) {
            $flashData['profanity_detected'] = 'Beberapa kata dalam pesan Anda telah disensor otomatis.';
        }

        return back()->with($flashData);
    }

    // ─────────────────────────────────────────────
    //  LAPORKAN BALASAN
    // ─────────────────────────────────────────────

    public function reportReply(Request $request, int $replyId)
    {
        $request->validate([
            'reason' => 'required|in:spam,kata_kasar,bullying,lainnya',
        ]);

        $reply    = ForumReply::findOrFail($replyId);
        $reporter = $request->user();

        // Tidak boleh melaporkan milik sendiri
        if ($reply->user_id === $reporter->id) {
            abort(403, 'Tidak bisa melaporkan pesan sendiri.');
        }

        // Cek apakah sudah pernah melapor
        $already = ReportedReply::where('reply_id', $replyId)
            ->where('reporter_id', $reporter->id)
            ->exists();

        if ($already) {
            return back()->with('error', 'Anda sudah pernah melaporkan pesan ini.');
        }

        ReportedReply::create([
            'reply_id'    => $replyId,
            'reporter_id' => $reporter->id,
            'reason'      => $request->reason,
            'status'      => 'pending',
        ]);

        return back()->with('success', 'Laporan Anda telah berhasil dikirim. Terima kasih.');
    }

    // ─────────────────────────────────────────────
    //  DAFTAR LAPORAN (Admin & Guru)
    // ─────────────────────────────────────────────

    public function reportedReplies(Request $request)
    {
        $user = $request->user();

        $query = ReportedReply::with([
            'reporter:id,name,role',
            'reply.user:id,name,role',
            'reply.topic:id,title,school_class_id',
            'reply.topic.schoolClass:id,name',
        ])->orderBy('created_at', 'desc');

        // Guru hanya melihat laporan di kelas yang dia ajar
        if ($user->role === 'guru') {
            $classIds = \App\Models\Schedule::where('teacher_id', $user->id)
                ->pluck('school_class_id');

            $query->whereHas('reply.topic', function ($q) use ($classIds) {
                $q->whereIn('school_class_id', $classIds);
            });
        }

        // Hitung total pending dan reviewed secara absolut (independen dari filter status saat ini)
        $pendingCount  = (clone $query)->where('status', 'pending')->count();
        $reviewedCount = (clone $query)->where('status', 'reviewed')->count();

        // Filter by status (hanya pending dan reviewed)
        $status = $request->query('status', 'pending');
        if (!in_array($status, ['pending', 'reviewed'])) {
            $status = 'pending';
        }
        $query->where('status', $status);

        $reports = $query->paginate(20)->withQueryString();

        return Inertia::render('Forum/Reports', [
            'reports'       => $reports,
            'activeStatus'  => $status,
            'pendingCount'  => $pendingCount,
            'reviewedCount' => $reviewedCount,
        ]);
    }

    /**
     * Update status laporan (reviewed) atau abaikan/hapus laporan.
     */
    public function updateReportStatus(Request $request, int $reportId)
    {
        $user   = $request->user();
        $report = ReportedReply::findOrFail($reportId);

        // Jika aksi abaikan (dismissed/ignored) atau method DELETE -> langsung hapus permanen dari database
        if ($request->isMethod('delete') || in_array($request->status, ['dismissed', 'ignored'])) {
            $report->delete();
            return back()->with('success', 'Laporan diabaikan dan telah dihapus dari sistem.');
        }

        $request->validate([
            'status' => 'required|in:reviewed',
        ]);

        $report->status = 'reviewed';
        $report->save();

        // Hapus / moderasi pesan forum yang melanggar jika belum dihapus
        if ($report->reply && $report->reply->deleted_by_moderator_id === null) {
            $report->reply->update([
                'content'                 => null,
                'deleted_by_moderator_id' => $user->id,
            ]);
        }

        return back()->with('success', 'Laporan berhasil diperbarui.');
    }

    /**
     * Hapus permanen laporan (abaikan).
     */
    public function destroyReport(Request $request, int $reportId)
    {
        $report = ReportedReply::findOrFail($reportId);
        $report->delete();

        return back()->with('success', 'Laporan diabaikan dan telah dihapus dari sistem.');
    }
}
