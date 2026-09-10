<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiometricCredential extends Model
{
    protected $fillable = [
        'user_id',
        'credential',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
