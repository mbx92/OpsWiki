<?php

namespace App\Services;

use App\Models\InboxItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Snippet;
use App\Models\Sop;
use App\Models\TroubleshootingCase;

class SearchService
{
    /**
     * @return array{pages: array, snippets: array, inbox: array, sops: array, troubleshooting: array, projects: array, mode: string}
     */
    public function search(string $query, int $limit = 10, bool $smart = false): array
    {
        $query = trim($query);

        if (strlen($query) < 2) {
            return $this->emptyResults('none');
        }

        if ($smart) {
            return array_merge($this->searchSmart($query, $limit), ['mode' => 'semantic']);
        }

        return array_merge($this->searchPostgres($query, $limit), ['mode' => 'keyword']);
    }

    /**
     * @return array{pages: array, snippets: array, inbox: array, sops: array, troubleshooting: array, projects: array}
     */
    private function searchSmart(string $query, int $limit): array
    {
        $pages = $this->searchPagesFullText($query, $limit);
        $rest = $this->searchPostgres($query, $limit);

        if (empty($pages)) {
            $pages = $rest['pages'];
        }

        return [
            'pages' => $pages,
            'snippets' => $rest['snippets'],
            'inbox' => $rest['inbox'],
            'sops' => $rest['sops'],
            'troubleshooting' => $rest['troubleshooting'],
            'projects' => $rest['projects'],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function searchPagesFullText(string $query, int $limit): array
    {
        try {
            return Page::query()
                ->select(['id', 'title', 'slug', 'summary', 'status', 'category_id'])
                ->selectRaw(
                    "ts_rank(to_tsvector('simple', coalesce(title,'') || ' ' || coalesce(summary,'') || ' ' || coalesce(content_markdown,'')), plainto_tsquery('simple', ?)) as rank",
                    [$query]
                )
                ->whereRaw(
                    "to_tsvector('simple', coalesce(title,'') || ' ' || coalesce(summary,'') || ' ' || coalesce(content_markdown,'')) @@ plainto_tsquery('simple', ?)",
                    [$query]
                )
                ->orderByDesc('rank')
                ->limit($limit)
                ->get()
                ->map(fn ($p) => $p->only(['id', 'title', 'slug', 'summary', 'status', 'category_id']))
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @return array{pages: array, snippets: array, inbox: array, sops: array, troubleshooting: array, projects: array}
     */
    private function searchPostgres(string $query, int $limit): array
    {
        $pages = Page::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('summary', 'ilike', "%{$query}%")
                    ->orWhere('content_markdown', 'ilike', "%{$query}%");
            })
            ->with('category')
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'summary', 'status', 'category_id'])
            ->map(fn ($p) => $p->toArray())
            ->all();

        $snippets = Snippet::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('command', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            })
            ->limit($limit)
            ->get(['id', 'title', 'command', 'platform'])
            ->map(fn ($p) => $p->toArray())
            ->all();

        $inbox = InboxItem::query()
            ->where('status', '!=', 'archived')
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('content', 'ilike', "%{$query}%");
            })
            ->limit($limit)
            ->get(['id', 'title', 'type', 'status'])
            ->map(fn ($p) => $p->toArray())
            ->all();

        $sops = Sop::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('purpose', 'ilike', "%{$query}%");
            })
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'status'])
            ->map(fn ($p) => $p->toArray())
            ->all();

        $troubleshooting = TroubleshootingCase::query()
            ->where(function ($q) use ($query) {
                $q->where('title', 'ilike', "%{$query}%")
                    ->orWhere('symptoms', 'ilike', "%{$query}%");
            })
            ->limit($limit)
            ->get(['id', 'title', 'slug', 'severity', 'status'])
            ->map(fn ($p) => $p->toArray())
            ->all();

        $projects = Project::query()
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                    ->orWhere('description', 'ilike', "%{$query}%");
            })
            ->limit($limit)
            ->get(['id', 'name', 'slug', 'status'])
            ->map(fn ($p) => $p->toArray())
            ->all();

        return compact('pages', 'snippets', 'inbox', 'sops', 'troubleshooting', 'projects');
    }

    /**
     * @return array{pages: array, snippets: array, inbox: array, sops: array, troubleshooting: array, projects: array, mode: string}
     */
    private function emptyResults(string $mode): array
    {
        return [
            'pages' => [],
            'snippets' => [],
            'inbox' => [],
            'sops' => [],
            'troubleshooting' => [],
            'projects' => [],
            'mode' => $mode,
        ];
    }
}
