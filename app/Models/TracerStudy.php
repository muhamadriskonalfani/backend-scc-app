<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerStudy extends Model
{
    protected $fillable = [
        'user_id',
        'full_name',
        'student_id_number',
        'faculty_id',
        'study_program_id',
        'entry_year',
        'graduation_year',
        'domicile',
        'whatsapp_number',
        'current_workplace',
        'current_job_duration_months',
        'company_scale',
        'job_title',
    ];

}
