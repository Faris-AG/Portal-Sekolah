<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property string $target_role
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<Announcement> query()
 * @method static \Illuminate\Database\Eloquent\Builder<Announcement> select($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder<Announcement> where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<Announcement> whereIn($column, $values, $boolean = 'and', $not = false)
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
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
