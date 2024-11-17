<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Получаем роли
        $adminRole = Role::where('code', 'ADMIN')->first();
        $userRole = Role::where('code', 'USER')->first();
        $guestRole = Role::where('code', 'GUEST')->first();

        // Получаем все разрешения для админа
        $allPermissions = Permission::all();

        // Привязываем все разрешения к администратору
        foreach ($allPermissions as $permission) {
            RolePermission::create([
                'role_id' => $adminRole->id,
                'permission_id' => $permission->id,
                'created_by' => 1,
            ]);
        }

        // Разрешения для пользователя
        $userPermissions = Permission::whereIn('code', [
            'GET-LIST_USER',
            'READ_USER',
            'UPDATE_USER',
        ])->get();

        foreach ($userPermissions as $permission) {
            RolePermission::create([
                'role_id' => $userRole->id,
                'permission_id' => $permission->id,
                'created_by' => 1,
            ]);
        }

        // Разрешения для гостя
        $guestPermissions = Permission::where('code', 'GET-LIST_USER')->get();

        foreach ($guestPermissions as $permission) {
            RolePermission::create([
                'role_id' => $guestRole->id,
                'permission_id' => $permission->id,
                'created_by' => 1,
            ]);
        }
    }
}
