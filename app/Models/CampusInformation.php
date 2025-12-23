<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampusInformation extends Model
{
    protected $fillable = [
        'image',
        'title',
        'description',
        'status',
        'created_by',
    ];

}
