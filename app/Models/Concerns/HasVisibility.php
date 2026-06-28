<?php

namespace App\Models\Concerns;

trait HasVisibility
{
    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function isInternal(): bool
    {
        return $this->visibility === 'internal';
    }

    public function isPrivate(): bool
    {
        return $this->visibility === 'private';
    }
}
