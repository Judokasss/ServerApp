<?php

namespace App\DTO\RolePermissionDTO;

class RolePermissionDTO
{
  public $permission_id;
  public $role_id;
  public $created_by;

  public function __construct($permission_id, $role_id, $created_by)
  {
    $this->permission_id = $permission_id;
    $this->role_id = $role_id;
    $this->created_by = $created_by;
  }

  // Метод для преобразования DTO в массив
  public function toArray(): array
  {
    return [
      'permission_id' => $this->permission_id,
      'role_id' => $this->role_id,
      'created_by' => $this->created_by,
    ];
  }
}
