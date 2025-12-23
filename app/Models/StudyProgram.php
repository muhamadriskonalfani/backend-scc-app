<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudyProgram extends Model
{
    protected $fillable = [
        'faculty_id',
        'name',
        'degree',
        'code',
    ];

}
