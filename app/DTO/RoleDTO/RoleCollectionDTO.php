<?php

namespace App\DTO\RoleDTO;

class RoleCollectionDTO
{
  private array $roles;

  public function __construct(array $roles)
  {
    $this->roles = $roles;
  }

  public function toArray(): array
  {
    return array_map(function ($role) {
      // Если $role — массив, удаляем ненужные ключи и преобразуем его в объект RoleDTO
      if (is_array($role)) {
        $filteredRole = array_intersect_key($role, array_flip(['name', 'description', 'code', 'created_by']));
        $role = new RoleDTO(...$filteredRole);
      }
      return $role->toArray();
    }, $this->roles);
  }
}
