<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function hasPermission(string $permission): bool
    {
        $this->loadMissing('permissions');

        foreach ($this->permissions as $assigned) {
            if ($assigned->slug === '*' || $assigned->slug === $permission) {
                return true;
            }

            if (str_ends_with($assigned->slug, '.*')) {
                $prefix = substr($assigned->slug, 0, -2);

                if ($permission === $prefix || str_starts_with($permission, $prefix.'.')) {
                    return true;
                }
            }
        }

        return false;
    }

    public function permissionSlugs(): array
    {
        $this->loadMissing('permissions');

        return $this->permissions->pluck('slug')->all();
    }
}
