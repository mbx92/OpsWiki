<?php

namespace App\Support;

class PlanFeatureCatalog
{
    /**
     * @return array<string, int>
     */
    public static function tierRanks(): array
    {
        return config('saas.tiers', []);
    }

    /**
     * @return array<string, string>
     */
    public static function features(): array
    {
        return config('saas.features', []);
    }

    /**
     * @return array<string, string>
     */
    public static function toolPlans(): array
    {
        return config('saas.tool_plans', []);
    }

    public static function tierRank(string $planSlug): int
    {
        return self::tierRanks()[$planSlug] ?? 0;
    }

    public static function requiredPlanForFeature(string $feature): ?string
    {
        return self::features()[$feature] ?? null;
    }

    public static function requiredPlanForTool(string $toolSlug): ?string
    {
        return self::toolPlans()[$toolSlug] ?? self::requiredPlanForFeature('tools');
    }

    public static function planHasFeature(string $planSlug, string $feature): bool
    {
        $required = self::requiredPlanForFeature($feature);

        if ($required === null) {
            return true;
        }

        return self::tierRank($planSlug) >= self::tierRank($required);
    }

    public static function planHasTool(string $planSlug, string $toolSlug): bool
    {
        $required = self::requiredPlanForTool($toolSlug);

        if ($required === null) {
            return true;
        }

        return self::tierRank($planSlug) >= self::tierRank($required);
    }

    /**
     * @return list<string>
     */
    public static function enabledFeaturesForPlan(string $planSlug): array
    {
        return array_values(array_filter(
            array_keys(self::features()),
            fn (string $feature) => self::planHasFeature($planSlug, $feature),
        ));
    }

    public static function upgradeMessage(string $feature): string
    {
        $required = self::requiredPlanForFeature($feature) ?? 'pro';
        $planName = ucfirst($required);

        return "This feature requires the {$planName} plan. Upgrade your workspace to unlock it.";
    }
}
