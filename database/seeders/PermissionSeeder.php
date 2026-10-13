<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'products',
            'categories',
            'inventory',
            'customers',
            'orders',
            'discounts',
            'tax',
            'shipping',
            'reviews',
            'cms',
            'seo',
            'notifications',
            'reports',
            'settings',
        ];

        $actions = [
            'view',
            'create',
            'edit',
            'delete',
        ];

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$action} {$module}",
                    'guard_name' => 'web',
                ]);
            }
        }
    }
}
