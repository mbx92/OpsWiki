<?php

namespace App\Services;

use App\Models\InboxItem;
use App\Models\Page;
use App\Models\Project;
use App\Models\Snippet;
use App\Models\Sop;
use App\Models\TroubleshootingCase;

class AssistantContextService
{
    private const EXCERPT_LIMIT = 1200;

    public function __construct(
        private SearchService $search,
    ) {}

    /**
     * @return array{results: array, context: string}
     */
    public function build(string $query): array
    {
        $results = $this->search->search($query, 5, smart: true);
        $context = $this->formatContext($results);

        return ['results' => $results, 'context' => $context];
    }

    /**
     * @param  array{pages?: array, snippets?: array, inbox?: array, sops?: array, troubleshooting?: array, projects?: array}  $results
     */
    private function formatContext(array $results): string
    {
        $chunks = [];

        foreach (array_slice($results['pages'] ?? [], 0, 3) as $item) {
            $page = Page::query()->find($item['id'] ?? null);
            if (! $page) {
                continue;
            }

            $chunks[] = $this->block('Wiki Page', $page->title, $this->excerpt(
                trim(($page->summary ?? '')."\n\n".($page->content_markdown ?? ''))
            ));
        }

        foreach (array_slice($results['snippets'] ?? [], 0, 2) as $item) {
            $snippet = Snippet::query()->find($item['id'] ?? null);
            if (! $snippet) {
                continue;
            }

            $body = "Command: {$snippet->command}\nPlatform: {$snippet->platform}\n".($snippet->description ?? '');
            $chunks[] = $this->block('Snippet', $snippet->title, $this->excerpt($body));
        }

        foreach (array_slice($results['sops'] ?? [], 0, 2) as $item) {
            $sop = Sop::query()->find($item['id'] ?? null);
            if (! $sop) {
                continue;
            }

            $body = implode("\n", array_filter([
                $sop->purpose,
                $sop->steps,
                $sop->validation,
                $sop->notes,
            ]));
            $chunks[] = $this->block('SOP', $sop->title, $this->excerpt($body));
        }

        foreach (array_slice($results['troubleshooting'] ?? [], 0, 2) as $item) {
            $case = TroubleshootingCase::query()->find($item['id'] ?? null);
            if (! $case) {
                continue;
            }

            $body = implode("\n", array_filter([
                'Symptoms: '.$case->symptoms,
                'Diagnosis: '.$case->diagnosis_steps,
                'Solution: '.$case->working_solution,
                'Prevention: '.$case->prevention,
            ]));
            $chunks[] = $this->block('Troubleshooting', $case->title, $this->excerpt($body));
        }

        foreach (array_slice($results['projects'] ?? [], 0, 2) as $item) {
            $project = Project::query()->find($item['id'] ?? null);
            if (! $project) {
                continue;
            }

            $body = implode("\n", array_filter([
                $project->description,
                $project->environment_notes,
                $project->server_location ? 'Server: '.$project->server_location : null,
            ]));
            $chunks[] = $this->block('Project', $project->name, $this->excerpt($body));
        }

        foreach (array_slice($results['inbox'] ?? [], 0, 1) as $item) {
            $inbox = InboxItem::query()->find($item['id'] ?? null);
            if (! $inbox) {
                continue;
            }

            $chunks[] = $this->block('Inbox', $inbox->title, $this->excerpt($inbox->content ?? ''));
        }

        return implode("\n\n---\n\n", $chunks);
    }

    private function block(string $type, string $title, string $body): string
    {
        return "[{$type}] {$title}\n".$body;
    }

    private function excerpt(string $text): string
    {
        $text = preg_replace('/\s+/u', ' ', trim($text)) ?? '';

        if (mb_strlen($text) <= self::EXCERPT_LIMIT) {
            return $text;
        }

        return mb_substr($text, 0, self::EXCERPT_LIMIT).'…';
    }
}
