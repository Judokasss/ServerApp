<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeLog extends Model
{
  use HasFactory;

  public $timestamps = false;

  protected $fillable = [
    'entity_type',
    'entity_id',
    'before',
    'after',
    'created_by',
  ];
}
