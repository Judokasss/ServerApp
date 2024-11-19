<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsChanges;

class Role extends Model
{
  use HasFactory, SoftDeletes, LogsChanges;

  public $timestamps = false;

  protected $fillable = [
    'name',
    'description',
    'code',
    'created_by',
  ];

  // Связь с разрешениями (многие ко многим)
  public function permissions()
  {
    return $this->belongsToMany(Permission::class, 'role_permissions');
  }
}
