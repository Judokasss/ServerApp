<?php

namespace App\Http\Requests\RolePermissionRequest;

use Illuminate\Foundation\Http\FormRequest;
use App\DTO\RolePermissionDTO\RolePermissionDTO;

class RolePermissionRequest extends FormRequest
{
  // Добавляем поля user_id и role_id в данные запроса, чтобы они стали доступны для валидации.
  protected function prepareForValidation(): void
  {
    $this->merge([
      'permission_id' => $this->route('permission_id'),
      'role_id' => $this->route('role_id')
    ]);
  }

  public function rules(): array
  {
    return [
      'permission_id' => 'required|exists:permissions,id',
      'role_id' => 'required|exists:roles,id',
    ];
  }

  // Метод преобразования данных запроса в DTO
  public function toDTO()
  {
    return new RolePermissionDTO(
      (int) $this->route('permission_id'),
      (int) $this->route('role_id'),
      (int) $this->user()->id
    );
  }
}
