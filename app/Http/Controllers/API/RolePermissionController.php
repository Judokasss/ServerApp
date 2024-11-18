<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\RolePermissionRequest\RolePermissionRequest;
use App\Http\Resources\RolePermissionResource;
use App\Models\RolePermission;
use App\DTO\RolePermissionDTO\RolePermissionDTO;
use App\DTO\RolePermissionDTO\RolePermissionCollectionDTO;
use Illuminate\Support\Facades\Auth;


class RolePermissionController extends Controller
{
  // Получение всех связей для конкретной роли по ID роли
  public function showRolePermission($roleId)
  {
    // Извлекаем все связи для конкретной роли по role_id
    $rolePermissions = RolePermission::where('role_id', $roleId)->get();

    // Преобразуем коллекцию моделей RolePermission в массив DTO
    $rolePermissionDTOs = $rolePermissions->map(function ($rolePermission) {
      return new RolePermissionDTO(
        $rolePermission->permission_id,
        $rolePermission->role_id,
        $rolePermission->created_by
      );
    })->toArray();

    // Оборачиваем массив DTO в коллекцию RolePermissionCollectionDTO
    $rolePermissionCollectionDTO = new RolePermissionCollectionDTO($rolePermissionDTOs);
    // Возвращаем результат
    return ($rolePermissionCollectionDTO->toArray() == null) ? response()->json(['message' => 'Role and permission not found'], 404) : $rolePermissionCollectionDTO->toArray();
  }


  // Создание новой связи роли с разрешениями
  public function storeRolePermission(RolePermissionRequest $request)
  {
    // Получаем DTO из данных запроса
    $rolePermissionDTO = $request->toDTO();

    // Создаем новую роль, используя данные из DTO
    $rolePermission = RolePermission::create($rolePermissionDTO->toArray());

    return (new RolePermissionResource($rolePermission))->response()->setStatusCode(201);
  }

  // Жесткое удаление связи роли с разрешениями
  public function destroyRolePermission($id)
  {
    // Находим связи пользователя и роли по ID
    $rolePermission = RolePermission::find($id);

    // Проверяем, существует ли роль
    if (!$rolePermission) {
      return response()->json(['message' => 'The permissions connection to the role was not found'], 404);
    }

    // Выполняем жесткое удаление
    $rolePermission->forceDelete();

    return response()->json(['message' => 'The permissions connection to the role permanently deleted'], 200);
  }

  // Мягкое удаление связи роли с разрешениями
  public function softDeleteRolePermission($id)
  {
    // Находим роль по ID
    $rolePermission = RolePermission::find($id);
    // Проверяем, существует ли роль
    if (!$rolePermission) {
      return response()->json(['message' => 'The permissions connection to the role was not found'], 404);
    }

    // Устанавливаем `deleted_by` текущим пользователем перед мягким удалением
    $rolePermission->deleted_by = Auth::id();
    $rolePermission->save();

    $rolePermission->delete(); // Использует soft delete
    return response()->json(['message' => 'The permissions connection to the role soft deleted'], 200);
  }

  // Восстановление мягко удаленной связи роли с разрешениями
  public function restoreRolePermission($id)
  {
    $rolePermission = RolePermission::onlyTrashed()->findOrFail($id);
    // Проверяем, существует ли роль
    if (!$rolePermission) {
      return response()->json(['message' => 'The permissions connection to the role was not found'], 404);
    }

    // Сбрасываем поле `deleted_by`
    $rolePermission->deleted_by = null;
    $rolePermission->save();

    $rolePermission->restore();
    return response()->json(['message' => 'The permissions connection to the role restored'], 200);
  }
}
