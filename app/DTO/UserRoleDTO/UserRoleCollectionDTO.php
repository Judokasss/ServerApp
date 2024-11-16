<?php

namespace App\DTO\UserRoleDTO;

class UserRoleCollectionDTO
{
    private array $userRoles;

    public function __construct(array $userRoles)
    {
        $this->userRoles = $userRoles;
    }

    public function toArray(): array
    {
        return array_map(function ($userRole) {
            // Проверяем, является ли $userRole массивом, и если да, фильтруем и преобразуем его в объект UserAndRoleDTO
            if (is_array($userRole)) {
                $filteredUserRole = array_intersect_key($userRole, array_flip(['user_id', 'role_id', 'created_by']));
                $userRole = new UserRoleDTO(
                    $filteredUserRole['user_id'] ?? null,
                    $filteredUserRole['role_id'] ?? null,
                    $filteredUserRole['created_by'] ?? null
                );
            }
            return $userRole->toArray();
        }, $this->userRoles);
    }
}
