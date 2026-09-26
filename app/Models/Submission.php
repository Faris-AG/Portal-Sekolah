<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Assignment;

class Submission extends Model
{
    protected $fillable = [
        'assignment_id',
        'student_id',
        'file_path',
        'file_name',
        'note',
        'submitted_at',
        'grade',
        'feedback',
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
