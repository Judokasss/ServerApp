<?php

namespace App\Services;

use App\Models\ChangeLog;

class ChangeLogService
{
  public function logChange(string $entityType, int $entityId, array $before, array $after, int $userId): void
  {
    ChangeLog::create([
      'entity_type' => $entityType,
      'entity_id' => $entityId,
      'before' => json_encode($before),
      'after' => json_encode($after),
      'created_by' => $userId,
    ]);
  }
}
