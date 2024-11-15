<?php

namespace App\Http\Requests\RoleRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\DTO\RoleDTO\RoleDTO;

class UpdateRoleRequest extends FormRequest
{
    public function rules(): array
    {
        $roleId = $this->route('id');
        return [
            'name' => 'required|string|max:255|unique:roles,name,' . $roleId,
            'code' => 'required|string|max:50|unique:roles,code,' . $roleId,
            'description' => 'nullable|string|max:1000',
        ];
    }

    // Метод для получения RoleDTO
    public function toRoleDTO(): RoleDTO
    {
        $data = $this->validated(); // Используем данные после валидации

        return new RoleDTO(
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['code'] ?? null,
            $this->user()->id
        );
    }
}
