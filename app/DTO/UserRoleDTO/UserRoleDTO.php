<?php

namespace App\DTO\UserRoleDTO;

class UserRoleDTO
{
    public $user_id;
    public $role_id;
    public $created_by;

    public function __construct($user_id, $role_id, $created_by)
    {
        $this->user_id = $user_id;
        $this->role_id = $role_id;
        $this->created_by = $created_by;
    }

    // Метод для преобразования DTO в массив
    public function toArray(): array
    {
        return [
            'user_id' => $this->user_id,
            'role_id' => $this->role_id,
            'created_by' => $this->created_by,
        ];
    }
}
