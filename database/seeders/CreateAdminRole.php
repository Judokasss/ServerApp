<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class CreateAdminRole extends Seeder
{
    public function run(): void
    {
        // Ищем пользователя с username = 'AlexAdmin'
        $user = User::where('username', 'AlexAdmin')->first();

        // Ищем роль ADMIN
        $adminRole = Role::where('code', 'ADMIN')->first();

        // Если пользователь и роль существуют, связываем их
        if ($user && $adminRole) {
            $user->roles()->attach($adminRole->id, ['created_by' => $user->id]);
        }
    }
}
