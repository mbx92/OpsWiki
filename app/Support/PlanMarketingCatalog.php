<?php

namespace App\Support;

class PlanMarketingCatalog
{
    /**
     * Technical feature gates enforced in the app (config/saas.php).
     *
     * @return array<string, array{label: string, min_plan: string, description: string}>
     */
    public static function gateDefinitions(): array
    {
        return [
            'books' => [
                'label' => 'Books',
                'min_plan' => 'pro',
                'description' => 'Organize wiki pages into books',
            ],
            'sops' => [
                'label' => 'SOPs',
                'min_plan' => 'pro',
                'description' => 'Standard operating procedures module',
            ],
            'troubleshooting' => [
                'label' => 'Troubleshooting',
                'min_plan' => 'pro',
                'description' => 'Incident and runbook cases',
            ],
            'projects' => [
                'label' => 'Projects',
                'min_plan' => 'pro',
                'description' => 'Project documentation module',
            ],
            'tools' => [
                'label' => 'Ops tools',
                'min_plan' => 'pro',
                'description' => 'MinIO IAM, pg_restore, Docker Compose, rclone builders',
            ],
            'wiki.import' => [
                'label' => 'Wiki import',
                'min_plan' => 'pro',
                'description' => 'Bulk import markdown files',
            ],
            'wiki.export' => [
                'label' => 'Static export',
                'min_plan' => 'pro',
                'description' => 'Export wiki or book as static HTML site',
            ],
            'custom_domain' => [
                'label' => 'Custom domain',
                'min_plan' => 'pro',
                'description' => 'Map docs.yourcompany.com to workspace',
            ],
            'assistant' => [
                'label' => 'AI assistant',
                'min_plan' => 'pro',
                'description' => 'In-app AI chat for documentation',
            ],
            'integrations' => [
                'label' => 'Integrations',
                'min_plan' => 'pro',
                'description' => 'MinIO archive, GlitchTip, AI provider settings',
            ],
            'users.manage' => [
                'label' => 'Team users',
                'min_plan' => 'pro',
                'description' => 'Invite and manage workspace members',
            ],
            'activity' => [
                'label' => 'Activity log',
                'min_plan' => 'team',
                'description' => 'Audit trail of create/update/delete actions',
            ],
            'roles.manage' => [
                'label' => 'Role permissions',
                'min_plan' => 'team',
                'description' => 'Customize Admin and User role permissions',
            ],
        ];
    }

    /**
     * Included on every plan — not gated, always available (with RBAC).
     *
     * @return list<string>
     */
    public static function includedOnAllPlans(): array
    {
        return [
            'Wiki pages (markdown editor & version history)',
            'Snippets & command library',
            'Inbox quick capture',
            'Full-text & smart search',
            'Public share links & portal',
            'Knowledge graph',
            'Assets upload',
            'Dashboard',
        ];
    }

    /**
     * Default marketing bullets shown on pricing — synced with real gates/limits.
     *
     * @return list<string>
     */
    public static function defaultMarketingFeatures(string $planSlug): array
    {
        return match ($planSlug) {
            'free' => [
                'Up to 3 workspace users',
                'Up to 100 wiki pages',
                'Wiki, snippets, inbox & assets',
                'Search & knowledge graph',
                'Public share links',
            ],
            'pro' => [
                'Unlimited users & wiki pages',
                'Books, SOPs, troubleshooting & projects',
                'Ops tools & AI assistant',
                'Wiki import & static export',
                'Integrations (MinIO, GlitchTip, AI)',
                'Custom domain & team user management',
            ],
            'team' => [
                'Everything in Pro',
                'Activity audit log',
                'Custom role permissions (Admin/User)',
            ],
            default => [],
        };
    }

    /**
     * @return list<string>
     */
    public static function enabledGateKeysForPlan(string $planSlug, ?array $storedGates = null): array
    {
        if (is_array($storedGates)) {
            return array_values($storedGates);
        }

        return collect(self::gatesForPlan($planSlug))
            ->filter(fn (array $gate) => $gate['enabled'])
            ->pluck('key')
            ->values()
            ->all();
    }

    /**
     * All marketing bullet options (union across plans).
     *
     * @return list<string>
     */
    public static function allMarketingOptions(): array
    {
        $options = [];

        foreach (['free', 'pro', 'team'] as $slug) {
            foreach (self::defaultMarketingFeatures($slug) as $label) {
                $options[$label] = true;
            }
        }

        return array_keys($options);
    }

    /**
     * @return list<array{key: string, label: string, min_plan: string, description: string}>
     */
    public static function allGateOptions(): array
    {
        return collect(self::gateDefinitions())
            ->map(fn (array $def, string $key) => [
                'key' => $key,
                'label' => $def['label'],
                'min_plan' => $def['min_plan'],
                'description' => $def['description'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{key: string, label: string, min_plan: string, description: string, enabled: bool}>
     */
    public static function gatesForPlan(string $planSlug): array
    {
        $rank = PlanFeatureCatalog::tierRank($planSlug);

        return collect(self::gateDefinitions())
            ->map(fn (array $def, string $key) => [
                'key' => $key,
                'label' => $def['label'],
                'min_plan' => $def['min_plan'],
                'description' => $def['description'],
                'enabled' => $rank >= PlanFeatureCatalog::tierRank($def['min_plan']),
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function defaultLimitsSummary(string $planSlug): array
    {
        $limits = config('saas.default_limits.'.$planSlug, []);

        $bullets = [];
        if (isset($limits['users'])) {
            $bullets[] = $limits['users'] === null
                ? 'Unlimited users'
                : "Up to {$limits['users']} users";
        }
        if (isset($limits['pages'])) {
            $bullets[] = $limits['pages'] === null
                ? 'Unlimited wiki pages'
                : "Up to {$limits['pages']} wiki pages";
        }

        return $bullets;
    }
}
