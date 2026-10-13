<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $allPermissions = [
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

        // Super Administrator gets everything.
        Role::findByName('Super Administrator')->syncPermissions(
            Permission::all()
        );

        // Store Administrator manages core store operations.
        Role::findByName('Store Administrator')->syncPermissions(
            $this->permissions([
                'products',
                'categories',
                'inventory',
                'customers',
                'orders',
                'discounts',
                'tax',
                'shipping',
                'reviews',
            ])
        );

        // Catalogue Manager
        Role::findByName('Catalogue Manager')->syncPermissions(
            $this->permissions([
                'products',
                'categories',
                'inventory',
            ])
        );

        // Order Manager
        Role::findByName('Order Manager')->syncPermissions(
            $this->permissions([
                'orders',
                'customers',
                'shipping',
            ])
        );

        // Marketing Manager
        Role::findByName('Marketing Manager')->syncPermissions(
            $this->permissions([
                'discounts',
                'reviews',
                'seo',
            ])
        );

        // Content Manager
        Role::findByName('Content Manager')->syncPermissions(
            $this->permissions([
                'cms',
                'seo',
            ])
        );

        // Customer Support
        Role::findByName('Customer Support')->syncPermissions(
            $this->permissions([
                'customers',
                'orders',
                'reviews',
            ])
        );

        // Reporting User
        Role::findByName('Reporting User')->syncPermissions(
            $this->permissions([
                'reports',
            ])
        );
    }

    private function permissions(array $modules): array
    {
        $permissions = [];

        foreach ($modules as $module) {
            foreach (['view', 'create', 'edit', 'delete'] as $action) {
                $permissions[] = "{$action} {$module}";
            }
        }

        return $permissions;
    }
}
