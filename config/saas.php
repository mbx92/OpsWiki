<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Central application domain
    |--------------------------------------------------------------------------
    |
    | Marketing, login, and platform admin live on this domain.
    | Tenant workspaces use subdomains: {slug}.{central_domain}
    |
    */
    'central_domain' => env('SAAS_CENTRAL_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'),

    /*
    |--------------------------------------------------------------------------
    | Custom domain DNS (CNAME target shown in workspace settings)
    |--------------------------------------------------------------------------
    |
    | Customers point their custom domain CNAME to this hostname.
    | Defaults to central_domain; override if app runs on a different edge host.
    |
    */
    'dns_cname_target' => env('SAAS_DNS_CNAME_TARGET', env(
        'SAAS_CENTRAL_DOMAIN',
        parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST) ?: 'localhost'
    )),

    /*
    | Optional public IPv4 for apex (root) A-record instructions.
    */
    'dns_a_record_ip' => env('SAAS_DNS_A_RECORD_IP'),

    /*
    |--------------------------------------------------------------------------
    | Default tenant (local / single-workspace installs)
    |--------------------------------------------------------------------------
    */
    'default_tenant_slug' => env('SAAS_DEFAULT_TENANT_SLUG', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */
    'registration_enabled' => env('SAAS_REGISTRATION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Auth rate limits (login & registration)
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'login' => [
            'max_attempts' => (int) env('SAAS_LOGIN_MAX_ATTEMPTS', 5),
            'decay_seconds' => (int) env('SAAS_LOGIN_DECAY_SECONDS', 60),
        ],
        'register' => [
            'max_attempts' => (int) env('SAAS_REGISTER_MAX_ATTEMPTS', 5),
            'decay_seconds' => (int) env('SAAS_REGISTER_DECAY_SECONDS', 3600),
        ],
    ],

    'default_plan_slug' => env('SAAS_DEFAULT_PLAN_SLUG', 'free'),

    /*
    |--------------------------------------------------------------------------
    | Default billing currency (ISO 4217)
    |--------------------------------------------------------------------------
    */
    'default_currency' => strtoupper((string) env('SAAS_DEFAULT_CURRENCY', 'USD')),

    /*
    |--------------------------------------------------------------------------
    | Default numeric limits per plan (used by seeder & marketing copy)
    |--------------------------------------------------------------------------
    */
    'default_limits' => [
        'free' => ['users' => 3, 'pages' => 100],
        'pro' => ['users' => null, 'pages' => null],
        'team' => ['users' => null, 'pages' => null],
    ],

    /*
    |--------------------------------------------------------------------------
    | Plan tiers (higher number = more features)
    |--------------------------------------------------------------------------
    */
    'tiers' => [
        'free' => 0,
        'pro' => 1,
        'team' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature gates — minimum plan slug required
    |--------------------------------------------------------------------------
    */
    'features' => [
        'books' => 'pro',
        'sops' => 'pro',
        'troubleshooting' => 'pro',
        'projects' => 'pro',
        'tools' => 'pro',
        'wiki.import' => 'pro',
        'wiki.export' => 'pro',
        'custom_domain' => 'pro',
        'assistant' => 'pro',
        'integrations' => 'pro',
        'users.manage' => 'pro',
        'activity' => 'team',
        'roles.manage' => 'team',
    ],

    /*
    |--------------------------------------------------------------------------
    | Per-tool plan requirements (falls back to tools feature above)
    |--------------------------------------------------------------------------
    */
    'tool_plans' => [
        'minio-iam-generator' => 'pro',
        'pg-restore-helper' => 'pro',
        'docker-compose-builder' => 'pro',
        'rclone-copy-builder' => 'pro',
    ],

    /*
    |--------------------------------------------------------------------------
    | Password policy
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min' => 8,
        'letters' => true,
        'mixed_case' => true,
        'numbers' => true,
        'symbols' => false,
        'uncompromised' => env('SAAS_PASSWORD_UNCOMPROMISED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Platform super admin (initial deploy / Coolify)
    |--------------------------------------------------------------------------
    */
    'super_admin' => [
        'name' => env('SUPER_ADMIN_NAME', 'OpsWiki Admin'),
        'email' => env('SUPER_ADMIN_EMAIL', 'admin@opswiki.com'),
        'password' => env('SUPER_ADMIN_PASSWORD'),
        'sync_password' => env('SUPER_ADMIN_SYNC_PASSWORD', false),
    ],

];
