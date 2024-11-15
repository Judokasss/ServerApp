<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
  public function toArray($request)
  {
    return [
      'name' => $this->name,
      'code' => $this->code,
      'description' => $this->description,
      'created_by' => $this->created_by,
    ];
  }
}
