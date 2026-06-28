<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasTags;
use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TroubleshootingCase extends Model
{
    use BelongsToTenant;
    use HasTags;
    use HasUniqueSlug;

    protected $table = 'troubleshooting_cases';

    protected $fillable = [
        'title',
        'slug',
        'symptoms',
        'environment',
        'error_log',
        'suspected_causes',
        'diagnosis_steps',
        'working_solution',
        'failed_attempts',
        'validation',
        'prevention',
        'severity',
        'status',
        'created_by',
        'updated_by',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
