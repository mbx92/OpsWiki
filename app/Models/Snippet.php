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
