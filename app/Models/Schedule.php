<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;

class Schedule extends Model
{
    protected $fillable = [
        'school_class_id', 'subject_id', 'teacher_id', 'day', 'start_time', 'end_time'
    ];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
