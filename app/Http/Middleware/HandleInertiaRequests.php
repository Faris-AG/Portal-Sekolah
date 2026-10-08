<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
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
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'unread_announcements_count' => function () use ($request) {
                    if (!$request->user()) return 0;
                    return \App\Models\Announcement::whereIn('target_role', ['all', $request->user()->role])
                        ->whereDoesntHave('readByUsers', function ($query) use ($request) {
                            $query->where('user_id', $request->user()->id)->whereNotNull('read_at');
                        })->count();
                },
                'latest_announcements' => function () use ($request) {
                    if (!$request->user()) return [];
                    $announcements = \App\Models\Announcement::whereIn('target_role', ['all', $request->user()->role])
                        ->with(['user' => function($q) { $q->select('id', 'name'); }])
                        ->with(['readByUsers' => function($q) use ($request) {
                            $q->where('user_id', $request->user()->id);
                        }])
                        ->orderBy('created_at', 'desc')
                        ->take(4)
                        ->get();
                        
                    return $announcements->map(function (\App\Models\Announcement $announcement) {
                        $isRead = $announcement->readByUsers->isNotEmpty() && $announcement->readByUsers->first()->pivot->read_at !== null;
                        $announcement->is_read = $isRead;
                        $announcement->unsetRelation('readByUsers');
                        return $announcement;
                    });
                },
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
