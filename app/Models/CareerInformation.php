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
        'approved_by',
    ];

}
