<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TenantProvisioningService
{
    public function createForUser(User $user, string $workspaceName, ?string $planSlug = null): Tenant
    {
        $planSlug ??= config('saas.default_plan_slug', 'free');
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->first()
            ?? Plan::where('is_active', true)->orderBy('sort_order')->firstOrFail();

        return DB::transaction(function () use ($user, $workspaceName, $plan) {
            $ownerRole = Role::where('slug', 'owner')->firstOrFail();

            $tenant = Tenant::create([
                'name' => $workspaceName,
                'slug' => Tenant::uniqueSlug($workspaceName),
                'status' => 'active',
            ]);

            Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'status' => 'active',
            ]);

            $tenant->users()->attach($user->id, ['role' => 'owner']);

            $user->update(['role_id' => $ownerRole->id]);

            return $tenant;
        });
    }
}
