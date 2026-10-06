<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  public function run(): void
    {
        $user = User::updateOrCreate(
            [
                'email' => 'admin@example.com',
            ],
            [
                'name' => 'Super Administrator',
                'password' => Hash::make('Password@123'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $role = Role::firstOrCreate([
            'name' => 'Super Administrator',
            'guard_name' => 'web',
        ]);

        $user->syncRoles([$role]);

        $this->command->info('Admin user created/updated successfully.');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: Password@123');
        $this->command->info('Role: Super Administrator');
    }
}
