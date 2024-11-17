<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $entities = ['user', 'role', 'permission'];
        $actions = ['get-list', 'read', 'create', 'update', 'delete', 'restore'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::create([
                    'name' => "{$action}-{$entity}",
                    'description' => ucfirst($action) . " permission for {$entity}",
                    'code' => strtoupper("{$action}_{$entity}"),
                    'created_by' => 1,
                ]);
            }
        }
    }
}
