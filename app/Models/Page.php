<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Concerns\HasVisibility;
use App\Models\Concerns\HasTags;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Page extends Model
{
    use BelongsToTenant;
    use HasTags;
    use HasVisibility;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content_markdown',
        'content_html',
        'category_id',
        'book_id',
        'sort_order',
        'status',
        'visibility',
        'created_by',
        'updated_by',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page) {
            if (empty($page->slug)) {
                $page->slug = static::uniqueSlug($page->title);
            }
        });
    }

    public static function uniqueSlug(string $title, ?int $exceptId = null): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))->exists()) {
            $slug = $original.'-'.$count++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assets(): MorphMany
    {
        return $this->morphMany(Asset::class, 'related');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(PageVersion::class)->orderByDesc('version_number');
    }

    public function isPubliclyAccessible(): bool
    {
        if ($this->isPublic()) {
            return true;
        }

        return $this->book?->isPublic() ?? false;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
