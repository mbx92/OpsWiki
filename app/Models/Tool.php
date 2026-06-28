<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tool extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'tool_type',
        'component_name',
        'config_schema',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'config_schema' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
