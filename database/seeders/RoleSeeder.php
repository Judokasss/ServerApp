<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create(['name' => 'Admin', 'description' => 'Administrator role', 'code' => 'ADMIN', 'created_by' => 1]);
        Role::create(['name' => 'User', 'description' => 'User role', 'code' => 'USER', 'created_by' => 1]);
        Role::create(['name' => 'Guest', 'description' => 'Guest role', 'code' => 'GUEST', 'created_by' => 1]);
    }
}
