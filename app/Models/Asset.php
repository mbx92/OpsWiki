<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Asset extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'filename',
        'original_name',
        'mime_type',
        'size',
        'disk',
        'bucket',
        'path',
        'url',
        'related_type',
        'related_id',
        'uploaded_by',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }
}
