<?php

namespace App\DTO\RolePermissionDTO;

class RolePermissionCollectionDTO
{
  private array $rolePermissions;

  public function __construct(array $rolePermissions)
  {
    $this->rolePermissions = $rolePermissions;
  }

  public function toArray(): array
  {
    // Преобразуем каждый RolePermissionDTO в массив
    return array_map(fn(RolePermissionDTO $rolePermission) => $rolePermission->toArray(), $this->rolePermissions);
  }
}
