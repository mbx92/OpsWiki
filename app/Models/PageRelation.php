<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageRelation extends Model
{
    public const TYPE_PAGES = 'pages';

    public const TYPE_SOPS = 'sops';

    public const TYPE_TROUBLESHOOTING = 'troubleshooting_cases';

    public const TYPE_SNIPPETS = 'snippets';

    public const TYPE_TOOLS = 'tools';

    public const TYPE_PROJECTS = 'projects';

    /** @var array<string, string> */
    public const DISPLAY_LABELS = [
        self::TYPE_PAGES => 'Wiki',
        self::TYPE_SOPS => 'SOPs',
        self::TYPE_TROUBLESHOOTING => 'Troubleshooting',
        self::TYPE_SNIPPETS => 'Snippets',
        self::TYPE_TOOLS => 'Tools',
        self::TYPE_PROJECTS => 'Projects',
    ];

    /** @var array<string, string> */
    public const LINKABLE_TYPES = [
        self::TYPE_PAGES => 'Wiki',
        self::TYPE_SOPS => 'SOP',
        self::TYPE_TROUBLESHOOTING => 'Troubleshooting',
        self::TYPE_SNIPPETS => 'Snippet',
        self::TYPE_PROJECTS => 'Project',
    ];

    /** @var array<string, class-string<Model>> */
    public const TYPE_MAP = [
        self::TYPE_PAGES => Page::class,
        self::TYPE_SOPS => Sop::class,
        self::TYPE_TROUBLESHOOTING => TroubleshootingCase::class,
        self::TYPE_SNIPPETS => Snippet::class,
        self::TYPE_TOOLS => Tool::class,
        self::TYPE_PROJECTS => Project::class,
    ];

    protected $fillable = [
        'source_type',
        'source_id',
        'target_type',
        'target_id',
        'relation_type',
    ];

    public static function typeForModel(Model $model): string
    {
        foreach (self::TYPE_MAP as $type => $class) {
            if ($model instanceof $class) {
                return $type;
            }
        }

        return $model->getMorphClass();
    }

    public static function resolveModel(string $type): ?string
    {
        return self::TYPE_MAP[$type] ?? null;
    }

    /**
     * @return list<array{id: int, type: string, title: string, slug: string|null, url: string|null}>
     */
    public static function relatedItemsFor(Model $model): array
    {
        $type = self::typeForModel($model);
        $id = $model->getKey();

        $relations = static::query()
            ->where(function ($q) use ($type, $id) {
                $q->where('source_type', $type)->where('source_id', $id);
            })
            ->orWhere(function ($q) use ($type, $id) {
                $q->where('target_type', $type)->where('target_id', $id);
            })
            ->get();

        $items = [];
        $seen = [];

        foreach ($relations as $relation) {
            $isSource = $relation->source_type === $type && (int) $relation->source_id === (int) $id;
            $otherType = $isSource ? $relation->target_type : $relation->source_type;
            $otherId = $isSource ? $relation->target_id : $relation->source_id;

            if ($otherType === $type && (int) $otherId === (int) $id) {
                continue;
            }

            $dedupeKey = $otherType.':'.$otherId;
            if (isset($seen[$dedupeKey])) {
                continue;
            }

            $class = self::resolveModel($otherType);
            if (! $class) {
                continue;
            }

            $record = $class::find($otherId);
            if (! $record) {
                continue;
            }

            $seen[$dedupeKey] = true;
            $items[] = self::formatRelatedItem($otherType, $record);
        }

        return $items;
    }

    /**
     * @return list<array{type: string, label: string, items: list<array{id: int, type: string, title: string, slug: string|null, url: string|null}>}>
     */
    public static function relatedItemsGroupedFor(Model $model): array
    {
        $items = self::relatedItemsFor($model);
        $grouped = [];

        foreach ($items as $item) {
            $type = $item['type'];
            if (! isset($grouped[$type])) {
                $grouped[$type] = [
                    'type' => $type,
                    'label' => self::DISPLAY_LABELS[$type]
                        ?? self::LINKABLE_TYPES[$type]
                        ?? ucfirst(str_replace('_', ' ', $type)),
                    'items' => [],
                ];
            }
            $grouped[$type]['items'][] = $item;
        }

        $order = [
            self::TYPE_PAGES,
            self::TYPE_SOPS,
            self::TYPE_TROUBLESHOOTING,
            self::TYPE_SNIPPETS,
            self::TYPE_TOOLS,
            self::TYPE_PROJECTS,
        ];

        $result = [];
        foreach ($order as $type) {
            if (isset($grouped[$type])) {
                $result[] = $grouped[$type];
            }
        }

        return $result;
    }

    public static function linkTo(Model $source, Model $target): void
    {
        $sourceType = self::typeForModel($source);
        $targetType = self::typeForModel($target);

        if ($sourceType === $targetType && (int) $source->getKey() === (int) $target->getKey()) {
            return;
        }

        $exists = static::query()
            ->where(function ($q) use ($sourceType, $source, $targetType, $target) {
                $q->where('source_type', $sourceType)
                    ->where('source_id', $source->getKey())
                    ->where('target_type', $targetType)
                    ->where('target_id', $target->getKey());
            })
            ->orWhere(function ($q) use ($sourceType, $source, $targetType, $target) {
                $q->where('source_type', $targetType)
                    ->where('source_id', $target->getKey())
                    ->where('target_type', $sourceType)
                    ->where('target_id', $source->getKey());
            })
            ->exists();

        if ($exists) {
            return;
        }

        static::create([
            'source_type' => $sourceType,
            'source_id' => $source->getKey(),
            'target_type' => $targetType,
            'target_id' => $target->getKey(),
            'relation_type' => 'related',
        ]);
    }

    /**
     * @return list<array{type: string, id: int}>
     */
    public static function relatedSelectionFor(Model $model): array
    {
        return array_map(
            fn (array $item) => ['type' => $item['type'], 'id' => $item['id']],
            self::relatedItemsFor($model),
        );
    }

    /**
     * @return list<array{type: string, label: string, items: list<array{id: int, title: string}>}>
     */
    public static function linkableCatalog(?string $exceptType = null, ?int $exceptId = null): array
    {
        $groups = [];

        foreach (self::LINKABLE_TYPES as $type => $label) {
            $class = self::TYPE_MAP[$type] ?? null;

            if (! $class) {
                continue;
            }

            $titleColumn = $type === self::TYPE_PROJECTS ? 'name' : 'title';
            $query = $class::query()->orderBy($titleColumn);

            if ($exceptType === $type && $exceptId) {
                $query->where('id', '!=', $exceptId);
            }

            $items = $query->get(['id', $titleColumn])
                ->map(fn ($record) => [
                    'id' => $record->id,
                    'title' => $record->{$titleColumn},
                ])
                ->all();

            if ($items !== []) {
                $groups[] = compact('type', 'label', 'items');
            }
        }

        return $groups;
    }

    /**
     * @return array<string, mixed>
     */
    public static function relatedValidationRules(): array
    {
        return [
            'related' => 'nullable|array',
            'related.*.type' => 'required|string|in:'.implode(',', array_keys(self::LINKABLE_TYPES)),
            'related.*.id' => 'required|integer',
        ];
    }

    /**
     * @param  list<array{type: string, id: int}>  $related
     */
    public static function syncFor(Model $model, array $related): void
    {
        $type = self::typeForModel($model);
        $id = $model->getKey();

        static::query()
            ->where(function ($q) use ($type, $id) {
                $q->where('source_type', $type)->where('source_id', $id);
            })
            ->orWhere(function ($q) use ($type, $id) {
                $q->where('target_type', $type)->where('target_id', $id);
            })
            ->delete();

        foreach ($related as $item) {
            if ($item['type'] === $type && (int) $item['id'] === (int) $id) {
                continue;
            }

            $class = self::resolveModel($item['type']);
            if (! $class) {
                continue;
            }

            $target = $class::find($item['id']);
            if (! $target) {
                continue;
            }

            self::linkTo($model, $target);
        }
    }

    /**
     * @return array{id: int, type: string, title: string, slug: string|null, url: string|null}
     */
    private static function formatRelatedItem(string $type, Model $record): array
    {
        $title = $record->title ?? $record->name ?? '#'.$record->getKey();
        $slug = $record->slug ?? null;

        $url = match ($type) {
            self::TYPE_PAGES => route('wiki.show', $slug),
            self::TYPE_SOPS => route('sops.show', $slug),
            self::TYPE_TROUBLESHOOTING => route('troubleshooting.show', $slug),
            self::TYPE_SNIPPETS => route('snippets.edit', $record->getKey()),
            self::TYPE_TOOLS => route('tools.show', $slug),
            self::TYPE_PROJECTS => route('projects.show', $slug),
            default => null,
        };

        return [
            'id' => $record->getKey(),
            'type' => $type,
            'title' => $title,
            'slug' => $slug,
            'url' => $url,
        ];
    }
}
