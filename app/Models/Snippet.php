<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Snippet extends Model
{
    use BelongsToTenant;
    use HasTags;

    /** @var list<string> */
    public const PLATFORMS = [
        'linux',
        'macos',
        'windows',
        'docker',
        'proxmox',
        'sql',
        'postgresql',
        'minio',
        'cloudflare',
        'tailscale',
    ];

    /** @var array<string, string> */
    public const PLATFORM_LABELS = [
        'macos' => 'macOS',
        'sql' => 'SQL Query',
        'postgresql' => 'PostgreSQL',
    ];

    /**
     * @return list<array{value: string, label: string}>
     */
    public static function platformOptions(): array
    {
        return array_map(fn (string $platform) => [
            'value' => $platform,
            'label' => self::PLATFORM_LABELS[$platform] ?? ucfirst($platform),
        ], self::PLATFORMS);
    }

    public static function platformLabel(?string $platform): ?string
    {
        if ($platform === null || $platform === '') {
            return null;
        }

        return self::PLATFORM_LABELS[$platform] ?? ucfirst($platform);
    }

    protected $fillable = [
        'title',
        'description',
        'command',
        'language',
        'platform',
        'variables',
        'category_id',
        'is_tested',
        'is_favorite',
        'last_used_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'variables' => 'array',
            'is_tested' => 'boolean',
            'is_favorite' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
