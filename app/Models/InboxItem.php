<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'title',
        'content',
        'type',
        'source',
        'priority',
        'tags',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
