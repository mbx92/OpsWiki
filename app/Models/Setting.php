<?php

namespace App\Models;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['tenant_id', 'key', 'value'];

    public $incrementing = false;

    protected $keyType = 'string';

    public static function getJson(string $key, array $default = []): array
    {
        $row = static::rowQuery($key)->first();

        if (! $row) {
            return $default;
        }

        $decoded = json_decode($row->value, true);

        return is_array($decoded) ? $decoded : $default;
    }

    public static function putJson(string $key, array $value): void
    {
        static::rowQuery($key)->updateOrCreate(
            static::rowAttributes($key),
            ['value' => json_encode($value)],
        );
    }

    public static function getMinio(): array
    {
        $stored = static::getJson('minio');
        $defaults = static::minioDefaults();

        $config = array_merge($defaults, array_intersect_key($stored, $defaults));

        if (! empty($stored['secret_key'])) {
            try {
                $config['secret_key'] = Crypt::decryptString($stored['secret_key']);
            } catch (\Throwable) {
                $config['secret_key'] = '';
            }
        }

        if (empty($config['access_key']) && env('AWS_ACCESS_KEY_ID')) {
            $config['access_key'] = env('AWS_ACCESS_KEY_ID');
            $config['secret_key'] = $config['secret_key'] ?: (string) env('AWS_SECRET_ACCESS_KEY', '');
            $config['bucket'] = $config['bucket'] ?: (string) env('AWS_BUCKET', '');
            $config['endpoint'] = $config['endpoint'] ?: (string) env('AWS_ENDPOINT', '');
            $config['region'] = $config['region'] ?: (string) env('AWS_DEFAULT_REGION', 'us-east-1');
            $config['use_path_style_endpoint'] = filter_var(
                env('AWS_USE_PATH_STYLE_ENDPOINT', $config['use_path_style_endpoint']),
                FILTER_VALIDATE_BOOL,
            );
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public static function minioForForm(): array
    {
        $config = static::getMinio();
        $stored = static::getJson('minio');

        return [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'endpoint' => $config['endpoint'] ?? '',
            'access_key' => $config['access_key'] ?? '',
            'bucket' => $config['bucket'] ?? '',
            'region' => $config['region'] ?? 'us-east-1',
            'use_path_style_endpoint' => (bool) ($config['use_path_style_endpoint'] ?? true),
            'archive_prefix' => $config['archive_prefix'] ?? 'archive',
            'archive_imports' => (bool) ($config['archive_imports'] ?? true),
            'archive_exports' => (bool) ($config['archive_exports'] ?? true),
            'archive_uploads' => (bool) ($config['archive_uploads'] ?? false),
            'has_secret_key' => ! empty($stored['secret_key']) || ! empty(env('AWS_SECRET_ACCESS_KEY')),
        ];
    }

    public static function saveMinio(array $input): void
    {
        $existing = static::getJson('minio');
        $payload = [
            'enabled' => (bool) ($input['enabled'] ?? false),
            'endpoint' => trim((string) ($input['endpoint'] ?? '')),
            'access_key' => trim((string) ($input['access_key'] ?? '')),
            'bucket' => trim((string) ($input['bucket'] ?? '')),
            'region' => trim((string) ($input['region'] ?? 'us-east-1')) ?: 'us-east-1',
            'use_path_style_endpoint' => (bool) ($input['use_path_style_endpoint'] ?? true),
            'archive_prefix' => trim((string) ($input['archive_prefix'] ?? 'archive')) ?: 'archive',
            'archive_imports' => (bool) ($input['archive_imports'] ?? true),
            'archive_exports' => (bool) ($input['archive_exports'] ?? true),
            'archive_uploads' => (bool) ($input['archive_uploads'] ?? false),
        ];

        $secret = trim((string) ($input['secret_key'] ?? ''));
        if ($secret !== '') {
            $payload['secret_key'] = Crypt::encryptString($secret);
        } elseif (! empty($existing['secret_key'])) {
            $payload['secret_key'] = $existing['secret_key'];
        }

        static::putJson('minio', $payload);
    }

    public static function getGlitchtip(): array
    {
        $stored = static::getJson('glitchtip');
        $defaults = static::glitchtipDefaults();

        $config = array_merge($defaults, array_intersect_key($stored, $defaults));

        if (! empty($stored['dsn'])) {
            try {
                $config['dsn'] = Crypt::decryptString($stored['dsn']);
            } catch (\Throwable) {
                $config['dsn'] = '';
            }
        }

        if (empty($config['dsn']) && env('SENTRY_LARAVEL_DSN')) {
            $config['dsn'] = (string) env('SENTRY_LARAVEL_DSN');
        } elseif (empty($config['dsn']) && env('SENTRY_DSN')) {
            $config['dsn'] = (string) env('SENTRY_DSN');
        }

        if (empty($config['environment']) && env('SENTRY_ENVIRONMENT')) {
            $config['environment'] = (string) env('SENTRY_ENVIRONMENT');
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public static function glitchtipForForm(): array
    {
        $config = static::getGlitchtip();
        $stored = static::getJson('glitchtip');

        return [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'environment' => $config['environment'] ?? config('app.env'),
            'traces_sample_rate' => (float) ($config['traces_sample_rate'] ?? 0),
            'send_default_pii' => (bool) ($config['send_default_pii'] ?? false),
            'has_dsn' => ! empty($stored['dsn']) || ! empty(env('SENTRY_LARAVEL_DSN')) || ! empty(env('SENTRY_DSN')),
            'dsn_hint' => static::maskDsn($config['dsn'] ?? ''),
        ];
    }

    public static function saveGlitchtip(array $input): void
    {
        $existing = static::getJson('glitchtip');
        $payload = [
            'enabled' => (bool) ($input['enabled'] ?? false),
            'environment' => trim((string) ($input['environment'] ?? config('app.env'))) ?: config('app.env'),
            'traces_sample_rate' => max(0, min(1, (float) ($input['traces_sample_rate'] ?? 0))),
            'send_default_pii' => (bool) ($input['send_default_pii'] ?? false),
        ];

        $dsn = trim((string) ($input['dsn'] ?? ''));
        if ($dsn !== '') {
            $payload['dsn'] = Crypt::encryptString($dsn);
        } elseif (! empty($existing['dsn'])) {
            $payload['dsn'] = $existing['dsn'];
        }

        static::putJson('glitchtip', $payload);
    }

    public static function getAi(): array
    {
        $stored = static::getJson('ai');
        $defaults = static::aiDefaults();

        $config = array_merge($defaults, array_intersect_key($stored, $defaults));

        if (! empty($stored['api_key'])) {
            try {
                $config['api_key'] = Crypt::decryptString($stored['api_key']);
            } catch (\Throwable) {
                $config['api_key'] = '';
            }
        }

        return $config;
    }

    /**
     * @return array<string, mixed>
     */
    public static function aiForForm(): array
    {
        $config = static::getAi();
        $stored = static::getJson('ai');

        return [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'base_url' => $config['base_url'] ?? '',
            'model' => $config['model'] ?? 'gpt-4o-mini',
            'system_prompt' => $config['system_prompt'] ?? '',
            'has_api_key' => ! empty($stored['api_key']),
        ];
    }

    public static function saveAi(array $input): void
    {
        $existing = static::getJson('ai');
        $payload = [
            'enabled' => (bool) ($input['enabled'] ?? false),
            'base_url' => trim((string) ($input['base_url'] ?? '')),
            'model' => trim((string) ($input['model'] ?? 'gpt-4o-mini')) ?: 'gpt-4o-mini',
            'system_prompt' => trim((string) ($input['system_prompt'] ?? '')),
        ];

        $apiKey = trim((string) ($input['api_key'] ?? ''));
        if ($apiKey !== '') {
            $payload['api_key'] = Crypt::encryptString($apiKey);
        } elseif (! empty($existing['api_key'])) {
            $payload['api_key'] = $existing['api_key'];
        }

        static::putJson('ai', $payload);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<static>
     */
    private static function rowQuery(string $key)
    {
        $tenantId = static::resolveTenantId();

        if (! $tenantId) {
            return static::query()->whereRaw('0 = 1');
        }

        return static::query()
            ->where('tenant_id', $tenantId)
            ->where('key', $key);
    }

    /**
     * @return array{tenant_id: int, key: string}
     */
    private static function rowAttributes(string $key): array
    {
        return [
            'tenant_id' => TenantContext::required()->id,
            'key' => $key,
        ];
    }

    private static function resolveTenantId(): ?int
    {
        if ($tenantId = TenantContext::id()) {
            return $tenantId;
        }

        static $fallbackTenantId = null;
        static $fallbackResolved = false;

        if ($fallbackResolved) {
            return $fallbackTenantId;
        }

        $fallbackResolved = true;
        $slug = config('saas.default_tenant_slug');

        if (! $slug) {
            return null;
        }

        $fallbackTenantId = Tenant::query()->where('slug', $slug)->value('id');

        return $fallbackTenantId ? (int) $fallbackTenantId : null;
    }

    /**
     * @return array<string, mixed>
     */
    private static function aiDefaults(): array
    {
        return [
            'enabled' => false,
            'base_url' => 'https://api.openai.com/v1',
            'api_key' => '',
            'model' => 'gpt-4o-mini',
            'system_prompt' => 'Jawab singkat dan praktis. Prioritaskan langkah yang bisa langsung dijalankan.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function glitchtipDefaults(): array
    {
        return [
            'enabled' => false,
            'dsn' => '',
            'environment' => config('app.env'),
            'traces_sample_rate' => 0,
            'send_default_pii' => false,
        ];
    }

    private static function maskDsn(string $dsn): string
    {
        if ($dsn === '') {
            return '';
        }

        $parsed = parse_url($dsn);

        if (! $parsed || empty($parsed['host'])) {
            return 'Configured';
        }

        $scheme = $parsed['scheme'] ?? 'https';
        $host = $parsed['host'].(isset($parsed['port']) ? ':'.$parsed['port'] : '');
        $path = $parsed['path'] ?? '';

        return $scheme.'://***@'.$host.$path;
    }

    /**
     * @return array<string, mixed>
     */
    private static function minioDefaults(): array
    {
        return [
            'enabled' => false,
            'endpoint' => '',
            'access_key' => '',
            'secret_key' => '',
            'bucket' => '',
            'region' => 'us-east-1',
            'use_path_style_endpoint' => true,
            'archive_prefix' => 'archive',
            'archive_imports' => true,
            'archive_exports' => true,
            'archive_uploads' => false,
        ];
    }
}
