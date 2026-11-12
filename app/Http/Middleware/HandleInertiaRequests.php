<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * Strategi performa:
     * - Semua closure di-evaluate lazy (hanya saat dipakai oleh Inertia).
     * - Data berat (reports count, announcements) di-cache 30 detik per user
     *   agar tidak menghantam DB di setiap perpindahan halaman.
     * - Cache di-invalidate secara eksplisit saat data berubah (dari controller).
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                // Hanya 7 kolom esensial — tidak bocorkan password/remember_token
                'user' => $request->user()?->only([
                    'id', 'name', 'email', 'role',
                    'email_verified_at', 'class_id', 'subject_id',
                ]),

                // ── Jumlah laporan pending (cache 30 detik per user) ──────────
                'pending_reports_count' => function () use ($request) {
                    $user = $request->user();
                    if (!$user || $user->role === 'siswa') return 0;

                    $cacheKey = "shared_reports_{$user->id}";

                    return Cache::remember($cacheKey, 30, function () use ($user) {
                        if ($user->role === 'admin') {
                            return \App\Models\ReportedReply::where('status', 'pending')->count();
                        }

                        // Guru: laporan hanya untuk kelas yang diajar
                        $classIds = \App\Models\Schedule::where('teacher_id', $user->id)
                            ->pluck('school_class_id');

                        if ($classIds->isEmpty()) return 0;

                        return \App\Models\ReportedReply::where('status', 'pending')
                            ->whereHas('reply.topic', function ($q) use ($classIds) {
                                $q->whereIn('school_class_id', $classIds);
                            })->count();
                    });
                },

                // ── Jumlah pengumuman belum dibaca (cache 30 detik per user) ──
                'unread_announcements_count' => function () use ($request) {
                    $user = $request->user();
                    if (!$user) return 0;

                    $cacheKey = "shared_unread_{$user->id}";

                    return Cache::remember($cacheKey, 30, function () use ($user) {
                        return \App\Models\Announcement::whereIn('target_role', ['all', $user->role])
                            ->whereDoesntHave('readByUsers', function ($q) use ($user) {
                                $q->where('user_id', $user->id)->whereNotNull('read_at');
                            })->count();
                    });
                },

                // ── 4 pengumuman terbaru (cache 30 detik per user) ────────────
                'latest_announcements' => function () use ($request) {
                    $user = $request->user();
                    if (!$user) return [];

                    $cacheKey = "shared_announcements_{$user->id}";

                    return Cache::remember($cacheKey, 30, function () use ($user) {
                        /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\Announcement> $announcements */
                        $announcements = \App\Models\Announcement::select([
                                'id', 'title', 'content', 'target_role', 'user_id', 'created_at',
                            ])
                            ->whereIn('target_role', ['all', $user->role])
                            ->with(['user' => fn ($q) => $q->select('id', 'name')])
                            ->with(['readByUsers' => fn ($q) => $q->where('user_id', $user->id)])
                            ->orderBy('created_at', 'desc')
                            ->take(4)
                            ->get();

                        foreach ($announcements as $ann) {
                            $ann->is_read = $ann->readByUsers->isNotEmpty()
                                && $ann->readByUsers->first()->pivot->read_at !== null;
                            $ann->unsetRelation('readByUsers');
                        }

                        return $announcements;
                    });
                },
            ],

            // Flash messages — tidak boleh di-cache (per-request)
            'flash' => [
                'success'            => $request->session()->get('success'),
                'error'              => $request->session()->get('error'),
                'profanity_detected' => $request->session()->get('profanity_detected'),
            ],
        ];
    }
}
