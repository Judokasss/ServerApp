<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'refresh_token',
        'refresh_expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
