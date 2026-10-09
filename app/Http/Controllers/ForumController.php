<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SchoolClass;
use App\Models\ForumTopic;
use App\Models\ForumReply;

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
                ->orderBy('updated_at', 'desc')
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

    public function show(Request $request, $id)
    {
        $topic = ForumTopic::with(['user', 'schoolClass', 'replies.user'])->findOrFail($id);
        
        $user = $request->user();
        if ($user->role === 'siswa' && $topic->school_class_id !== $user->class_id) {
            abort(403);
        }

        return Inertia::render('Forum/Show', [
            'topic' => $topic,
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

        ForumTopic::create([
            'school_class_id' => $request->school_class_id,
            'user_id' => $user->id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return back()->with('success', 'Topik diskusi berhasil dibuat.');
    }

    public function storeReply(Request $request, $id)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $topic = ForumTopic::findOrFail($id);
        $user = $request->user();

        if ($user->role === 'siswa' && $topic->school_class_id !== $user->class_id) {
            abort(403);
        }

        ForumReply::create([
            'forum_topic_id' => $topic->id,
            'user_id' => $user->id,
            'content' => $request->content,
        ]);
        
        // Update topic updated_at so it bumps to the top
        $topic->touch();

        return back()->with('success', 'Balasan berhasil dikirim.');
    }
}
