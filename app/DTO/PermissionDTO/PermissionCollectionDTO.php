<?php

namespace App\DTO\PermissionDTO;

class PermissionCollectionDTO
{
    private array $permissions;

    public function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }

    public function toArray(): array
    {
        return array_map(function ($permission) {
            // Если $permission — массив, удаляем ненужные ключи и преобразуем его в объект PermissionDTO
            if (is_array($permission)) {
                $filteredPermission = array_intersect_key($permission, array_flip(['name', 'description', 'code', 'created_by']));
                $permission = new PermissionDTO(...$filteredPermission);
            }
            return $permission->toArray();
        }, $this->permissions);
    }
}
