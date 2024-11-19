<?php

namespace App\DTO\ChangeLogDTO;

class ChangeLogDTO
{
  public $entity_type;
  public $entity_id;
  public $before;
  public $after;
  public $created_by;

  public function __construct($entity_type, $entity_id, $before, $after, $created_by)
  {
    $this->entity_type = $entity_type;
    $this->entity_id = $entity_id;
    // преобразуем строки JSON в массив
    $this->before = is_string($before) ? json_decode($before, true) : $before;
    $this->after = is_string($after) ? json_decode($after, true) : $after;
    $this->created_by = $created_by;
  }

  public function toArray(): array
  {
    return [
      'entity_type' => $this->entity_type,
      'entity_id' => $this->entity_id,
      'before' => $this->before,
      'after' => $this->after,
      'created_by' => $this->created_by,
    ];
  }
}
