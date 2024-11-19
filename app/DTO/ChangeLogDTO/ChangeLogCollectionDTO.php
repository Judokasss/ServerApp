<?php

namespace App\DTO\ChangeLogDTO;

class ChangeLogCollectionDTO
{
  private array $logs;

  public function __construct(array $logs)
  {
    $this->logs = $logs;
  }

  public function toArray(): array
  {
    return array_map(fn(ChangeLogDTO $log) => $log->toArray(), $this->logs);
  }
}
