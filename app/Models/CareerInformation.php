<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareerInformation extends Model
{
    protected $fillable = [
        'info_type',
        'image',
        'title',
        'description',
        'company_name',
        'location',
        'status',
        'created_by',
        'faculty_id',
        'approved_by',
        'approved_at',
        'expired_at',
        'application_link',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
