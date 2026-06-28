<?php

namespace App\Services;

use App\Models\Page;
use App\Models\Tenant;

class TenantUsageService
{
    /**
     * @return array{users: int, pages: int}
     */
    public function for(Tenant $tenant): array
    {
        return [
            'users' => $tenant->users()->count(),
            'pages' => Page::withoutGlobalScope('tenant')
                ->where('tenant_id', $tenant->id)
                ->count(),
        ];
    }
}
