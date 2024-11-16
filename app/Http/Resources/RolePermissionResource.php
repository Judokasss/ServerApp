<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RolePermissionResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'role_id' => $this->role_id,
      'permission_id' => $this->permission_id,
      'created_by' => $this->created_by,
    ];
  }
}
