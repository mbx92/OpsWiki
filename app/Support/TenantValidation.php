<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class TenantValidation
{
    public static function exists(string $table, string $column = 'id'): Rule
    {
        $tenantId = TenantContext::id();

        return Rule::exists($table, $column)->when(
            $tenantId !== null,
            fn ($rule) => $rule->where('tenant_id', $tenantId),
        );
    }

    public static function unique(string $table, string $column, ?int $ignoreId = null): Rule
    {
        $tenantId = TenantContext::id();
        $rule = Rule::unique($table, $column);

        if ($ignoreId !== null) {
            $rule = $rule->ignore($ignoreId);
        }

        return $rule->when(
            $tenantId !== null,
            fn (Rule $scoped) => $scoped->where('tenant_id', $tenantId),
        );
    }
}
