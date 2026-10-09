<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'target_role',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function readByUsers()
    {
        return $this->belongsToMany(User::class, 'announcement_user')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }
}
