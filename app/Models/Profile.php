<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $fillable = [
        'user_id',
        'student_id_number',
        'faculty_id',
        'study_program_id',
        'entry_year',
        'graduation_year',
        'image',
        'phone',
        'testimonial',
        'bio',
        'education',
        'skills',
        'experience',
        'linkedin_url',
        'cv_file',
    ];

}
