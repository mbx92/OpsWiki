<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenantRelation
{
    public static function bootBelongsToTenantRelation(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = TenantContext::id();
            $table = $builder->getModel()->getTable();

            if ($tenantId) {
                $builder->where(function (Builder $query) use ($table, $tenantId) {
                    $query->where("{$table}.tenant_id", $tenantId);

                    if ($tenantId === static::resolveDefaultTenantIdForRelation()) {
                        $query->orWhereNull("{$table}.tenant_id");
                    }
                });
            } else {
                $builder->whereRaw('0 = 1');
            }
        });

        static::creating(function (Model $model) {
            if (! $model->getAttribute('tenant_id') && TenantContext::id()) {
                $model->setAttribute('tenant_id', TenantContext::id());
            }
        });
    }

    private static function resolveDefaultTenantIdForRelation(): ?int
    {
        static $tenantId = null;
        static $resolved = false;

        if ($resolved) {
            return $tenantId;
        }

        $resolved = true;
        $slug = config('saas.default_tenant_slug');

        if (! $slug) {
            return null;
        }

        $tenantId = Tenant::query()->where('slug', $slug)->value('id');

        return $tenantId ? (int) $tenantId : null;
    }
}
