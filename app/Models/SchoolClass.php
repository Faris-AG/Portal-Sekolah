<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $grade_level
 * @property string|null $wali_kelas
 * @property int|null $teacher_id
 * @method static int count($columns = '*')
 * @method static \Illuminate\Database\Eloquent\Builder<SchoolClass> latest($column = null)
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class SchoolClass extends Model
{
    protected $fillable = ['name', 'grade_level', 'wali_kelas', 'teacher_id'];

    public function students()
    {
        return $this->hasMany(User::class, 'class_id');
    }

    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
