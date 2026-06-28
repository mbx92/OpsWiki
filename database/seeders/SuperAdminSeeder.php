<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) config('saas.super_admin.email');
        $name = (string) config('saas.super_admin.name');
        $password = config('saas.super_admin.password');
        $ownerRole = Role::where('slug', 'owner')->first();

        $user = User::firstOrNew(['email' => $email]);
        $user->fill([
            'name' => $name,
            'is_super_admin' => true,
            'is_active' => true,
            'role_id' => $ownerRole?->id ?? $user->role_id,
        ]);

        if (! $user->email_verified_at) {
            $user->email_verified_at = now();
        }

        if ($password && (! $user->exists || config('saas.super_admin.sync_password'))) {
            $user->password = $password;
        }

        $user->save();
    }
}
