<?php

use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\RoleController;
use App\Http\Controllers\API\PermissionController;
use App\Http\Controllers\API\UserRoleController;
use App\Http\Controllers\API\RolePermissionController;

Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);

Route::middleware(['auth.custom'])->group(function () {
  Route::get('/auth/me', [AuthController::class, 'me']);
  Route::post('/auth/out', [AuthController::class, 'logout']);
  Route::get('/auth/tokens', [AuthController::class, 'tokens']);
  Route::post('/auth/out_all', [AuthController::class, 'logoutAll']);
  Route::post('/auth/change_password', [AuthController::class, 'changePassword']);
  Route::post('/auth/refresh', [AuthController::class, 'refresh']);


  /* РОЛИ */
  // Получение списка ролей
  Route::get('policy/role', [RoleController::class, 'indexRole']);
  // Получение конкретной роли
  Route::get('policy/role/{id}', [RoleController::class, 'showRole']);
  // Создание роли
  Route::post('policy/role', [RoleController::class, 'storeRole']);
  // Обновление роли
  Route::put('policy/role/{id}', [RoleController::class, 'updateRole']);
  // Жесткое удаление разрешения
  Route::delete('policy/role/{id}', [RoleController::class, 'destroyRole']);
  // Мягкое удаление роли
  Route::delete('policy/role/{id}/soft', [RoleController::class, 'softDeleteRole']);
  // Восстановление мягко удаленной роли
  Route::post('policy/role/{id}/restore', [RoleController::class, 'restoreRole']);


  /* РАЗРЕШЕНИЯ */
  Route::get('policy/permission', [PermissionController::class, 'indexPermission']);
  // Получение конкретного разрешения
  Route::get('policy/permission/{id}', [PermissionController::class, 'showPermission']);
  // Создание разрешения
  Route::post('policy/permission', [PermissionController::class, 'storePermission']);
  // Обновление разрешения
  Route::put('policy/permission/{id}', [PermissionController::class, 'updatePermission']);
  // Мягкое удаление разрешений
  Route::delete('policy/permission/{id}/soft', [PermissionController::class, 'softDeletePermission']);
  // Восстановление мягко удаленного разрешения
  Route::post('policy/permission/{id}/restore', [PermissionController::class, 'restorePermission']);


  /* ПОЛУЧЕНИЕ СПИСКА ПОЛЬЗОВАТЕЛЕЙ */
  Route::get('policy/users', [UserRoleController::class, 'UserCollection']);
  // Получение конкретного пользователя
  Route::get('policy/user/{id}', [UserRoleController::class, 'showUser']);
  // Создание связи пользователя и роли
  Route::post('policy/user/{user_id}/role/{role_id}', [UserRoleController::class, 'storeUserRole']);
  // Жесткое удаление связи пользователя и роли
  Route::delete('policy/userRole/{id}', [UserRoleController::class, 'destroyUserRole']);
  // Мягкое удаление связи пользователя и роли
  Route::delete('policy/userRole/{id}/soft', [UserRoleController::class, 'softDeleteUserRole']);
  // Восстановление мягко удаленноq связи пользователя и роли
  Route::post('policy/userRole/{id}/restore', [UserRoleController::class, 'restoreUserRole']);


  /* Получение конкретной связи роли с разрешениями */
  Route::get('policy/rolePermission/{role_id}', [RolePermissionController::class, 'showRolePermission']);
  // Создание связи роли с разрешениями
  Route::post('policy/role/{role_id}/permission/{permission_id}', [RolePermissionController::class, 'storeRolePermission']);
  // Жесткое удаление связи роли с разрешениями
  Route::delete('policy/rolePermission/{id}', [RolePermissionController::class, 'destroyRolePermission']);
  // Мягкое удаление связи роли с разрешениями
  Route::delete('policy/rolePermission/{id}/soft', [RolePermissionController::class, 'softDeleteRolePermission']);
  // Восстановление мягко удаленной связи роли с разрешениями
  Route::post('policy/rolePermission/{id}/restore', [RolePermissionController::class, 'restoreRolePermission']);
});
