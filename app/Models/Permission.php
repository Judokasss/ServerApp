<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsChanges;

class Permission extends Model
{
    use HasFactory, SoftDeletes, LogsChanges;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'description',
        'created_by'
    ];
}
