<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\UserRoleController;
use App\Http\Controllers\API\RolePermissionController;
use App\Http\Controllers\API\ChangeLogController;
use App\Http\Middleware\CheckPermission;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware(['auth.custom'])->group(function () {
  Route::get('/auth/me', [AuthController::class, 'me']);
  Route::post('/auth/out', [AuthController::class, 'logout']);
  Route::get('/auth/tokens', [AuthController::class, 'tokens']);
  Route::post('/auth/out_all', [AuthController::class, 'logoutAll']);
  Route::post('/auth/change_password', [AuthController::class, 'changePassword']);
  Route::post('/auth/refresh', [AuthController::class, 'refresh']);

  // Маршрут для обновления профиля с middleware проверки аутентификации
  Route::put('/auth/profile', [AuthController::class, 'updateProfile'])->middleware(CheckPermission::class . ':UPDATE_USER');

  /* РОЛИ */

  // Получение списка ролей
  Route::get('/policy/role', [RoleController::class, 'indexRole'])->middleware(CheckPermission::class . ':GET-LIST_ROLE');
  // Получение конкретной роли
  Route::get('policy/role/{id}', [RoleController::class, 'showRole'])->middleware(CheckPermission::class . ':READ_ROLE');
  // Создание роли
  Route::post('policy/role', [RoleController::class, 'storeRole'])->middleware(CheckPermission::class . ':CREATE_ROLE');
  // Обновление роли
  Route::put('policy/role/{id}', [RoleController::class, 'updateRole'])->middleware(CheckPermission::class . ':UPDATE_ROLE');
  // Жесткое удаление разрешения
  Route::delete('policy/role/{id}', [RoleController::class, 'destroyRole'])->middleware(CheckPermission::class . ':DELETE_ROLE');
  // Мягкое удаление роли
  Route::delete('policy/role/{id}/soft', [RoleController::class, 'softDeleteRole'])->middleware(CheckPermission::class . ':DELETE_ROLE');
  // Восстановление мягко удаленной роли
  Route::post('policy/role/{id}/restore', [RoleController::class, 'restoreRole'])->middleware(CheckPermission::class . ':RESTORE_ROLE');
  // Получение истории изменения записи роли
  Route::get('policy/role/{entityId}/story', [RoleController::class, 'roleStory'])->middleware(CheckPermission::class . ':GET-STORY_ROLE');

  /* РАЗРЕШЕНИЯ */

  // Получение списка разрешений
  Route::get('policy/permission', [PermissionController::class, 'indexPermission'])->middleware(CheckPermission::class . ':GET-LIST_PERMISSION');
  // Получение конкретного разрешения
  Route::get('policy/permission/{id}', [PermissionController::class, 'showPermission'])->middleware(CheckPermission::class . ':READ_PERMISSION');
  // Создание разрешения
  Route::post('policy/permission', [PermissionController::class, 'storePermission'])->middleware(CheckPermission::class . ':CREATE_PERMISSION');
  // Обновление разрешения
  Route::put('policy/permission/{id}', [PermissionController::class, 'updatePermission'])->middleware(CheckPermission::class . ':UPDATE_PERMISSION');
  // Жесткое удаление разрешений
  Route::delete('policy/permission/{id}', [PermissionController::class, 'destroyPermission'])->middleware(CheckPermission::class . ':DELETE_PERMISSION');
  // Мягкое удаление разрешений
  Route::delete('policy/permission/{id}/soft', [PermissionController::class, 'softDeletePermission'])->middleware(CheckPermission::class . ':DELETE_PERMISSION');
  // Восстановление мягко удаленного разрешения
  Route::post('policy/permission/{id}/restore', [PermissionController::class, 'restorePermission'])->middleware(CheckPermission::class . ':RESTORE_PERMISSION');
  // Получение истории изменения записи разрешения
  Route::get('policy/permission/{entity_id}/story', [PermissionController::class, 'permissionStory'])->middleware(CheckPermission::class . ':GET-STORY_PERMISSION');

  /* ПОЛУЧЕНИЕ СПИСКА ПОЛЬЗОВАТЕЛЕЙ */

  // Получение списка пользователей
  Route::get('policy/users', [UserRoleController::class, 'UserCollection'])->middleware(CheckPermission::class . ':GET-LIST_USER');
  // Получение конкретного пользователя
  Route::get('policy/user/{id}', [UserRoleController::class, 'showUser'])->middleware(CheckPermission::class . ':READ_USER');
  // Создание связи пользователя и роли
  Route::post('policy/user/{user_id}/role/{role_id}', [UserRoleController::class, 'storeUserRole'])->middleware(CheckPermission::class . ':CREATE_PERMISSION');
  // Жесткое удаление связи пользователя и роли
  Route::delete('policy/userRole/{id}', [UserRoleController::class, 'destroyUserRole'])->middleware(CheckPermission::class . ':DELETE_USER');
  // Мягкое удаление связи пользователя и роли
  Route::delete('policy/userRole/{id}/soft', [UserRoleController::class, 'softDeleteUserRole'])->middleware(CheckPermission::class . ':DELETE_USER');
  // Восстановление мягко удаленной связи пользователя и роли
  Route::post('policy/userRole/{id}/restore', [UserRoleController::class, 'restoreUserRole'])->middleware(CheckPermission::class . ':RESTORE_USER');
  // Получение истории изменения записи пользователя
  Route::get('policy/user/{entity_id}/story', [UserRoleController::class, 'userStory'])->middleware(CheckPermission::class . ':GET-STORY_USER');

  /* СВЯЗИ РОЛИ С РАЗРЕШЕНИЯМИ  */

  //Получение конкретной связи роли с разрешениями
  Route::get('policy/rolePermission/{role_id}', [RolePermissionController::class, 'showRolePermission'])->middleware(CheckPermission::class . ':READ_ROLE');
  // Создание связи роли с разрешениями
  Route::post('policy/role/{role_id}/permission/{permission_id}', [RolePermissionController::class, 'storeRolePermission'])->middleware(CheckPermission::class . ':CREATE_ROLE');
  // Жесткое удаление связи роли с разрешениями
  Route::delete('policy/rolePermission/{id}', [RolePermissionController::class, 'destroyRolePermission'])->middleware(CheckPermission::class . ':DELETE_ROLE');
  // Мягкое удаление связи роли с разрешениями
  Route::delete('policy/rolePermission/{id}/soft', [RolePermissionController::class, 'softDeleteRolePermission'])->middleware(CheckPermission::class . ':DELETE_ROLE');
  // Восстановление мягко удаленной связи роли с разрешениями
  Route::post('policy/rolePermission/{id}/restore', [RolePermissionController::class, 'restoreRolePermission'])->middleware(CheckPermission::class . ':RESTORE_ROLE');


  Route::post('/changelog/restore/{id}', [ChangeLogController::class, 'restoreEntity']);
});
