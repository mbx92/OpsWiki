<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Tag extends Model
{
    use BelongsToTenant;

    protected $fillable = ['name', 'slug', 'color'];

    public function pages(): MorphToMany
    {
        return $this->morphedByMany(Page::class, 'taggable');
    }

    public function snippets(): MorphToMany
    {
        return $this->morphedByMany(Snippet::class, 'taggable');
    }
}
