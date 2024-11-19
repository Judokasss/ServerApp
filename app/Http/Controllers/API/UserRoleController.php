<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRoleRequest\UserRoleRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserRoleResource;
use App\Http\Resources\ChangeLogResource;
use App\Models\User;
use App\Models\UserRole;
use App\Models\ChangeLog;
use App\DTO\UserDTO\UserCollectionDTO;
use App\DTO\UserDTO\UserDTO;
use App\DTO\ChangeLogDTO\ChangeLogDTO;
use App\DTO\ChangeLogDTO\ChangeLogCollectionDTO;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;



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
        DB::beginTransaction(); // Начинаем транзакцию

        try {
            // Получаем DTO из данных запроса
            $userRoleDTO = $request->toDTO();

            // Создаем новую роль, используя данные из DTO
            $userRole = UserRole::create($userRoleDTO->toArray());

            DB::commit(); // Подтверждаем транзакцию

            return (new UserRoleResource($userRole))->response()->setStatusCode(201);
        } catch (\Exception $e) {
            DB::rollBack(); // Откатываем транзакцию в случае ошибки
            return response()->json(['message' => 'Failed to store user-role association'], 500);
        }
    }

    // Жесткое удаление связи пользователя и роли
    public function destroyUserRole($id)
    {
        DB::beginTransaction();

        try {
            // Находим связь пользователя и роли по ID
            $userRole = UserRole::find($id);

            if (!$userRole) {
                return response()->json(['message' => 'The user-role connection was not found'], 404);
            }

            // Выполняем жесткое удаление
            $userRole->forceDelete();

            DB::commit();

            return response()->json(['message' => 'The user-role connection permanently deleted'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete user-role connection'], 500);
        }
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

        // Устанавливаем `deleted_by` текущим пользователем перед мягким удалением
        $userRole->deleted_by = Auth::id();
        $userRole->save();

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

        // Сбрасываем поле `deleted_by`
        $userRole->deleted_by = null;
        $userRole->save();

        $userRole->restore();
        return response()->json(['message' => 'The users connection to the role restored'], 200);
    }

    // Получение истории изменения записи пользователя по id
    public function userStory($entityId)
    {
        // Извлекаем все связи для конкретной роли по role_id
        $users = ChangeLog::where('entity_id', $entityId)->get();

        // Преобразуем коллекцию моделей RolePermission в массив DTO
        $usersDTOs = $users->map(function ($userLog) {
            return new ChangeLogDTO(
                $userLog->entity_type,
                $userLog->entity_id,
                $userLog->before,
                $userLog->after,
                $userLog->created_by,
            );
        })->toArray();

        // Оборачиваем массив DTO в коллекцию RolePermissionCollectionDTO
        $changeLogCollectionDTO = new ChangeLogCollectionDTO($usersDTOs);

        // Возвращаем результат
        return response()->json(new ChangeLogResource($changeLogCollectionDTO->toArray()), 200);
    }
}
