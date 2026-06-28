<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use BelongsToTenant;
    use HasTags;
    use HasUniqueSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'repository_url',
        'production_url',
        'staging_url',
        'server_location',
        'environment_notes',
        'created_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
