<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Faculty extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    public function studyPrograms()
    {
        return $this->hasMany(StudyProgram::class);
    }

    public function tracerStudies()
    {
        return $this->hasMany(TracerStudy::class);
    }

    public function adminProfiles()
    {
        return $this->hasMany(AdminProfile::class);
    }
}
