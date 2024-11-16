<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest\UserRoleRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserRoleResource;
use App\Models\User;
use App\Models\UserRole;
use App\DTO\UserDTO\UserCollectionDTO;
use App\DTO\UserDTO\UserDTO;


class UserRoleController extends Controller
{
    // Получение списка пользователей
    public function UserCollection()
    {
        $users = User::all()->toArray(); // Получаем массив ролей из базы данных
        $userCollectionDTO = new UserCollectionDTO($users); // Создаем коллекцию DTO

        return response()->json($userCollectionDTO->toArray()); // Возвращаем JSON
    }

    // Получение конкретного пользователя по ID
    public function showUser($id)
    {
        // Извлекаем роль по id
        $user = User::findOrFail($id);
        // Преобразуем модель Role в DTO
        $userDTO = new UserDTO(
            $user->id,
            $user->username,
            $user->email,
            $user->birthday
        );

        // Возвращаем DTO через RoleResource
        return new UserResource($userDTO);
    }

    // Создание новой связи пользователя и роли
    public function storeUserRole(UserRoleRequest $request)
    {
        // Получаем DTO из данных запроса
        $userRoleDTO = $request->toDTO();

        // Создаем новую роль, используя данные из DTO
        $userRole = UserRole::create($userRoleDTO->toArray());

        return response()->json([
            'message' => 'User role created successfully',
            'data' => (new UserRoleResource($userRole))->resolve()
        ], 201);
    }

    // Жесткое удаление связи пользователя и роли
    public function destroyUserRole($id)
    {
        // Находим связи пользователя и роли по ID
        $userRole = UserRole::find($id);

        // Проверяем, существует ли роль
        if (!$userRole) {
            return response()->json(['message' => 'The users connection to the role was not found'], 404);
        }

        // Выполняем жесткое удаление
        $userRole->forceDelete();

        return response()->json(['message' => 'The users connection to the role permanently deleted'], 200);
    }

    // Мягкое удаление связи пользователя и роли
    public function softDeleteUserRole($id)
    {
        // Находим роль по ID
        $userRole = UserRole::find($id);
        // Проверяем, существует ли роль
        if (!$userRole) {
            return response()->json(['message' => 'The users connection to the role was not found'], 404);
        }
        $userRole->delete(); // Использует soft delete
        return response()->json(['message' => 'The users connection to the role soft deleted'], 200);
    }

    // Восстановление мягко удаленноq связи пользователя и роли
    public function restoreUserRole($id)
    {
        $userRole = UserRole::onlyTrashed()->findOrFail($id);
        // Проверяем, существует ли роль
        if (!$userRole) {
            return response()->json(['message' => 'The users connection to the role was not found'], 404);
        }
        $userRole->restore();
        return response()->json(['message' => 'The users connection to the role restored'], 200);
    }
}