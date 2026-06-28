<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(?User $user, string $action, ?Model $subject = null, ?string $label = null, array $properties = []): void
    {
        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : 'system',
            'subject_id' => $subject?->getKey(),
            'subject_label' => $label ?? $this->resolveLabel($subject),
            'properties' => $properties ?: null,
            'created_at' => now(),
        ]);
    }

    private function resolveLabel(?Model $subject): ?string
    {
        if (! $subject) {
            return null;
        }

        return $subject->title
            ?? $subject->name
            ?? $subject->original_name
            ?? null;
    }
}
