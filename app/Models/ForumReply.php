<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForumReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'forum_topic_id',
        'user_id',
        'content',
        'deleted_by_moderator_id',
        'is_edited',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
    ];

    public function topic()
    {
        return $this->belongsTo(ForumTopic::class, 'forum_topic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'deleted_by_moderator_id');
    }

    public function reports()
    {
        return $this->hasMany(ReportedReply::class, 'reply_id');
    }

    /**
     * Apakah pesan ini sudah ditombstone (dihapus oleh moderator)?
     */
    public function isTombstoned(): bool
    {
        return $this->content === null && $this->deleted_by_moderator_id !== null;
    }
}
