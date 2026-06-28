<?php

namespace App\Models\Concerns;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function syncTags(array $tagNames): void
    {
        $tagIds = collect($tagNames)
            ->filter()
            ->map(fn (string $name) => Tag::firstOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($name)],
                ['name' => $name],
            )->id)
            ->all();

        $this->tags()->sync($tagIds);
    }
}
