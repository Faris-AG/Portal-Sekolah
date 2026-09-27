<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
