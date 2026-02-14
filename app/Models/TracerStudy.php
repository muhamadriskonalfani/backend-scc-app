<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TracerStudy extends Model
{
    protected $fillable = [
        'user_id',
        // 'full_name',
        'student_id_number',
        'faculty_id',
        'study_program_id',
        'entry_year',
        'graduation_year',
        // 'domicile',
        // 'whatsapp_number',
        'employment_status',
        'current_workplace',
        'company_scale',
        'job_title',
        'job_category',
        'employment_type',
        'employment_sector',
        'monthly_income_range',
        'job_study_relevance_level',
        'suggestion_for_university',
        // 'current_job_duration_months',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function studyProgram()
    {
        return $this->belongsTo(StudyProgram::class);
    }
}
