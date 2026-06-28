<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ArchiveRecord extends Model
{
    protected $fillable = [
        'type',
        'original_name',
        'stored_path',
        'bucket',
        'size',
        'mime_type',
        'related_type',
        'related_id',
        'metadata',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'size' => 'integer',
        ];
    }

    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
