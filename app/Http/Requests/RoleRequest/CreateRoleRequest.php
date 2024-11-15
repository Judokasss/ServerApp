<?php

namespace App\Http\Requests\RoleRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\DTO\RoleDTO\RoleDTO;

class CreateRoleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|unique:roles,name|max:255',
            'code' => 'required|string|unique:roles,code|max:50',
            'description' => 'nullable|string|max:1000',
        ];
    }

    // Метод преобразования данных запроса в DTO
    public function toDTO()
    {
        return new RoleDTO(
            $this->input('name'),
            $this->input('description'),
            $this->input('code'),
            (int) $this->user()->id
        );
    }
}
