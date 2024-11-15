<?php

namespace App\Http\Requests\PermissionRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\DTO\PermissionDTO\PermissionDTO;

class UpdatePermissionRequest extends FormRequest
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
    public function toPermissionDTO(): PermissionDTO
    {
        $data = $this->validated(); // Используем данные после валидации

        return new PermissionDTO(
            $data['name'] ?? null,
            $data['description'] ?? null,
            $data['code'] ?? null,
            $this->user()->id
        );
    }
}
