<?php

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            $tenantId = TenantContext::id();
            $table = $builder->getModel()->getTable();

            if ($tenantId) {
                $builder->where(function (Builder $query) use ($table, $tenantId) {
                    $query->where("{$table}.tenant_id", $tenantId);

                    // Legacy rows created before tenant backfill (default workspace only).
                    if ($tenantId === static::resolveDefaultTenantId()) {
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

    private static function resolveDefaultTenantId(): ?int
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

    public function tenant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
