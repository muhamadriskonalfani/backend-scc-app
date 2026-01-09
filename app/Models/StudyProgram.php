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

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function tracerStudies()
    {
        return $this->hasMany(TracerStudy::class);
    }
}
