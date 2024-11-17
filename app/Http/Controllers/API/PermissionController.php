<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Http\Requests\PermissionRequest\CreatePermissionRequest;
use App\Http\Requests\PermissionRequest\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\DTO\PermissionDTO\PermissionDTO;
use App\DTO\PermissionDTO\PermissionCollectionDTO;
use Illuminate\Support\Facades\Auth;

class PermissionController extends Controller
{
    // Получение списка разрешений
    public function indexPermission()
    {
        $permissions = Permission::all()->toArray(); // Получаем массив ролей из базы данных
        $permissionCollectionDTO = new PermissionCollectionDTO($permissions); // Создаем коллекцию DTO

        return response()->json($permissionCollectionDTO->toArray()); // Возвращаем JSON
    }

    // Получение конкретного разрешения по ID
    public function showPermission($id)
    {
        // Извлекаем роль по id
        $permission = Permission::findOrFail($id);
        // Преобразуем модель Role в DTO
        $permissionDTO = new PermissionDTO(
            $permission->name,
            $permission->description,
            $permission->code,
            $permission->created_by
        );

        // Возвращаем DTO через PermissionResource
        return new PermissionResource($permissionDTO);
    }

    // Создание нового разрешения
    public function storePermission(CreatePermissionRequest $request)
    {
        // Получаем DTO из данных запроса
        $permissionDTO = $request->toDTO();

        // Создаем новую роль, используя данные из DTO
        $permission = Permission::create($permissionDTO->toArray());

        return (new PermissionResource($permission))->response()->setStatusCode(201);
    }

    // Обновление существующего разрешения
    public function updatePermission(UpdatePermissionRequest $request, $id)
    {
        // Находим модель по ID
        $permission = Permission::findOrFail($id);
        $permissionDTO = $request->toPermissionDTO();  // Получение DTO из запроса
        $permission->update($permissionDTO->toArray());
        return response()->json(new PermissionResource($permission), 200);
    }

    // Жесткое удаление роли по ID
    public function destroyPermission($id)
    {
        // Находим роль по ID
        $permission = Permission::find($id);

        // Проверяем, существует ли роль
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        // Выполняем жесткое удаление
        $permission->forceDelete();

        return response()->json(['message' => 'Permission permanently deleted'], 200);
    }

    // Мягкое удаление роли
    public function softDeletePermission($id)
    {
        // Находим роль по ID
        $permission = Permission::find($id);
        // Проверяем, существует ли роль
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        // Устанавливаем `deleted_by` текущим пользователем перед мягким удалением
        $permission->deleted_by = Auth::id();
        $permission->save();

        $permission->delete(); // Использует soft delete
        return response()->json(['message' => 'Permission soft deleted'], 200);
    }

    // Восстановление мягко удаленной роли
    public function restorePermission($id)
    {
        $permission = Permission::onlyTrashed()->findOrFail($id);
        // Проверяем, существует ли роль
        if (!$permission) {
            return response()->json(['message' => 'Permission not found'], 404);
        }

        // Сбрасываем поле `deleted_by`
        $permission->deleted_by = null;
        $permission->save();

        $permission->restore();
        return response()->json(['message' => 'Permission restored'], 200);
    }
}
