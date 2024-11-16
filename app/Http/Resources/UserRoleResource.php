<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserRoleResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'user_id' => $this->user_id,
      'role_id' => $this->role_id,
      'created_by' => $this->created_by,
    ];
  }
}
