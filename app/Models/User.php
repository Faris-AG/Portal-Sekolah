<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string $role
 * @property int|null $class_id
 * @property int|null $subject_id
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<User> query()
 * @method static \Illuminate\Database\Eloquent\Builder<User> verified()
 * @method static \Illuminate\Database\Eloquent\Builder<User> where($column, $operator = null, $value = null, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<User> whereNull($columns, $boolean = 'and', $not = false)
 * @method static \Illuminate\Database\Eloquent\Builder<User> whereNotNull($columns, $boolean = 'and')
 * @method static \Illuminate\Database\Eloquent\Builder<User> whereIn($column, $values, $boolean = 'and', $not = false)
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
#[Fillable(['name', 'email', 'password', 'role', 'class_id', 'subject_id', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, MassPrunable, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function waliClass()
    {
        return $this->hasOne(SchoolClass::class, 'teacher_id');
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function readAnnouncements()
    {
        return $this->belongsToMany(Announcement::class, 'announcement_user')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Query untuk MassPrunable: hapus akun belum verifikasi yang sudah > 15 menit.
     */
    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        return static::whereNull('email_verified_at')
                     ->where('updated_at', '<', now()->subMinutes(15));
    }

    /**
     * Local scope: hanya user yang sudah verifikasi email.
     */
    public function scopeVerified(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereNotNull('email_verified_at');
    }
}
