<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageRelation;
use App\Models\Project;
use App\Models\Sop;
use App\Models\Snippet;
use App\Models\TroubleshootingCase;

class KnowledgeGraphService
{
    /**
     * @return array{
     *     entries: list<array{id: string, label: string, type: string, url: string|null, related: list<array{id: string, label: string, type: string, url: string|null}>, relation_count: int}>,
     *     orphans: list<array{id: string, label: string, type: string, url: string|null}>,
     *     stats: array{total: int, connected: int, orphans: int, relations: int},
     *     types: list<string>
     * }
     */
    public function build(): array
    {
        $nodes = [];
        $seen = [];

        $this->collectNodes($nodes, $seen, PageRelation::TYPE_PAGES, Page::query()->orderBy('title')->get(['id', 'title', 'slug']));
        $this->collectNodes($nodes, $seen, PageRelation::TYPE_SOPS, Sop::query()->orderBy('title')->get(['id', 'title', 'slug']));
        $this->collectNodes($nodes, $seen, PageRelation::TYPE_TROUBLESHOOTING, TroubleshootingCase::query()->orderBy('title')->get(['id', 'title', 'slug']));
        $this->collectNodes($nodes, $seen, PageRelation::TYPE_PROJECTS, Project::query()->orderBy('name')->get(['id', 'name', 'slug']), 'name');
        $this->collectNodes($nodes, $seen, PageRelation::TYPE_SNIPPETS, Snippet::query()->orderBy('title')->get(['id', 'title']));

        $adjacency = [];

        foreach (PageRelation::query()->get() as $relation) {
            $source = $this->nodeId($relation->source_type, (int) $relation->source_id);
            $target = $this->nodeId($relation->target_type, (int) $relation->target_id);

            if (! isset($seen[$source], $seen[$target])) {
                continue;
            }

            $adjacency[$source][$target] = true;
            $adjacency[$target][$source] = true;
        }

        $relations = PageRelation::query()->count();

        $connectedIds = array_keys(array_filter($adjacency, fn (array $neighbors) => count($neighbors) > 0));
        $orphanIds = array_values(array_diff(array_keys($nodes), $connectedIds));

        $entries = [];

        foreach ($connectedIds as $id) {
            $relatedIds = array_keys($adjacency[$id] ?? []);
            $related = [];

            foreach ($relatedIds as $relatedId) {
                if (isset($nodes[$relatedId])) {
                    $related[] = $nodes[$relatedId];
                }
            }

            usort($related, fn (array $a, array $b) => [$a['type'], $a['label']] <=> [$b['type'], $b['label']]);

            $entries[] = [
                ...$nodes[$id],
                'related' => $related,
                'relation_count' => count($related),
            ];
        }

        usort($entries, fn (array $a, array $b) => [$b['relation_count'], $a['label']] <=> [$a['relation_count'], $b['label']]);

        $orphans = array_map(fn (string $id) => $nodes[$id], $orphanIds);
        usort($orphans, fn (array $a, array $b) => [$a['type'], $a['label']] <=> [$b['type'], $b['label']]);

        $types = array_values(array_unique(array_column(array_values($nodes), 'type')));
        sort($types);

        return [
            'entries' => $entries,
            'orphans' => $orphans,
            'stats' => [
                'total' => count($nodes),
                'connected' => count($connectedIds),
                'orphans' => count($orphanIds),
                'relations' => $relations,
            ],
            'types' => $types,
        ];
    }

    /**
     * @param  array<string, array{id: string, label: string, type: string, url: string|null}>  $nodes
     * @param  array<string, true>  $seen
     */
    private function collectNodes(array &$nodes, array &$seen, string $type, $records, string $labelKey = 'title'): void
    {
        foreach ($records as $record) {
            $id = $this->nodeId($type, (int) $record->id);
            $label = $record->{$labelKey} ?? '#'.$record->id;
            $slug = $record->slug ?? null;

            $url = match ($type) {
                PageRelation::TYPE_PAGES => route('wiki.show', $slug),
                PageRelation::TYPE_SOPS => route('sops.show', $slug),
                PageRelation::TYPE_TROUBLESHOOTING => route('troubleshooting.show', $slug),
                PageRelation::TYPE_PROJECTS => route('projects.show', $slug),
                PageRelation::TYPE_SNIPPETS => route('snippets.edit', $record->id),
                default => null,
            };

            $nodes[$id] = compact('id', 'label', 'type') + ['url' => $url];
            $seen[$id] = true;
        }
    }

    private function nodeId(string $type, int $id): string
    {
        return $type.'-'.$id;
    }
}
