<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $ownerRole = Role::where('slug', 'owner')->first();
        $userRole = Role::where('slug', 'user')->first();

        User::updateOrCreate(
            ['email' => config('saas.super_admin.email', 'admin@wiki.com')],
            [
                'name' => config('saas.super_admin.name', 'Admin'),
                'password' => config('saas.super_admin.password', 'password'),
                'email_verified_at' => now(),
                'role_id' => $ownerRole?->id,
                'is_active' => true,
                'is_super_admin' => true,
            ],
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'email_verified_at' => now(),
                'role_id' => $userRole?->id,
                'is_active' => true,
            ],
        );
    }
}

