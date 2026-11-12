<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Announcement;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class GlobalAnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $search = $request->query('search', '');
        $status = $request->query('status', 'semua');
        
        $query = Announcement::whereIn('target_role', ['all', $user->role])
            ->with(['user' => function($q) { $q->select('id', 'name'); }])
            ->with(['readByUsers' => function($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->orderBy('created_at', 'desc');
            
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }
        
        if ($status === 'belum') {
            $query->whereDoesntHave('readByUsers', function($q) use ($user) {
                $q->where('user_id', $user->id)->whereNotNull('read_at');
            });
        }
        
        /** @var \Illuminate\Database\Eloquent\Collection<int, Announcement> $announcements */
        $announcements = $query->get();

        foreach ($announcements as $ann) {
            $isRead = $ann->readByUsers->isNotEmpty() && $ann->readByUsers->first()->pivot->read_at !== null;
            $ann->is_read = $isRead;
            $ann->unsetRelation('readByUsers');
        }
        
        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
            'filters' => [
                'search' => $search,
                'status' => $status,
            ]
        ]);
    }
    
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        $announcement = Announcement::whereIn('target_role', ['all', $user->role])->findOrFail($id);
        
        $hasRead = DB::table('announcement_user')
            ->where('announcement_id', $id)
            ->where('user_id', $user->id)
            ->exists();
            
        if (!$hasRead) {
            DB::table('announcement_user')->insert([
                'announcement_id' => $id,
                'user_id' => $user->id,
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Cache::forget("shared_unread_{$user->id}");
            Cache::forget("shared_announcements_{$user->id}");
        }
        
        return redirect()->back();
    }
    
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $unread = Announcement::whereIn('target_role', ['all', $user->role])
            ->whereDoesntHave('readByUsers', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })->pluck('id');
            
        if ($unread->isNotEmpty()) {
            $data = $unread->map(function($id) use ($user) {
                return [
                    'announcement_id' => $id,
                    'user_id' => $user->id,
                    'read_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
            
            DB::table('announcement_user')->insert($data);

            Cache::forget("shared_unread_{$user->id}");
            Cache::forget("shared_announcements_{$user->id}");
        }
        
        return redirect()->back()->with('success', 'Semua pengumuman telah ditandai sebagai dibaca.');
    }
}
