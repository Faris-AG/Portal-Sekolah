<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportedReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'reply_id',
        'reporter_id',
        'reason',
        'status',
    ];

    public function reply()
    {
        return $this->belongsTo(ForumReply::class, 'reply_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }
}
