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
        'expired_at',
        'created_by',
        'approved_by',
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
