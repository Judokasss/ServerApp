<?php

namespace App\Http\Requests\UserRoleRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\DTO\UserRoleDTO\UserRoleDTO;

class UserRoleRequest extends FormRequest
{
    // Добавляем поля user_id и role_id в данные запроса, чтобы они стали доступны для валидации.
    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->route('user_id'),
            'role_id' => $this->route('role_id')
        ]);
    }

    public function rules(): array
    {
        return [
            'user_id' => "required|exists:users,id",
            'role_id' => 'required|exists:roles,id',
        ];
    }

    // Метод преобразования данных запроса в DTO
    public function toDTO()
    {
        return new UserRoleDTO(
            (int) $this->route('user_id'),
            (int) $this->route('role_id'),
            (int) $this->user()->id
        );
    }
}
