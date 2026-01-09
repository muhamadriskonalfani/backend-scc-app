<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusInformation extends Model
{
    protected $fillable = [
        'faculty_id',
        'image',
        'title',
        'description',
        'status',
        'created_by',
    ];

    public function faculty()
    {
        return $this->belongsTo(Faculty::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
