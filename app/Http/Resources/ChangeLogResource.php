<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChangeLogResource extends JsonResource
{
  public function toArray($request)
  {
    if (is_array($this->resource)) {
      return array_map(fn($log) => [
        'before' => $log['before'],
        'after' => $log['after'],
      ], $this->resource);
    }
  }
}
