<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

class TenantValidation
{
    public static function exists(string $table, string $column = 'id'): Exists
    {
        $tenantId = TenantContext::id();

        return Rule::exists($table, $column)->when(
            $tenantId !== null,
            fn (Exists $rule) => $rule->where('tenant_id', $tenantId),
        );
    }

    public static function unique(string $table, string $column, ?int $ignoreId = null): Unique
    {
        $tenantId = TenantContext::id();
        $rule = Rule::unique($table, $column);

        if ($ignoreId !== null) {
            $rule = $rule->ignore($ignoreId);
        }

        return $rule->when(
            $tenantId !== null,
            fn (Unique $scoped) => $scoped->where('tenant_id', $tenantId),
        );
    }
}
