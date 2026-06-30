<?php

namespace App\Http\Controllers\Concerns;

use App\Services\ProjectDocumentationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait LinksContentToProject
{
    protected function linkProjectFromRequest(Request $request, Model $content): void
    {
        $project = app(ProjectDocumentationService::class)->resolveFromRequest($request);

        if ($project) {
            app(ProjectDocumentationService::class)->linkToProject($project, $content);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function projectCreateContext(Request $request): array
    {
        $docs = app(ProjectDocumentationService::class);
        $project = $docs->resolveFromRequest($request);

        if (! $project) {
            return ['linkProject' => null, 'prefill' => []];
        }

        $prefill = [];

        if ($request->filled('title')) {
            $prefill['title'] = (string) $request->query('title');
        }

        return [
            'linkProject' => [
                'slug' => $project->slug,
                'name' => $project->name,
                'url' => route('projects.show', $project),
            ],
            'prefill' => $prefill,
        ];
    }

    protected function linkProjectValidationRules(): array
    {
        return [
            'link_project' => 'nullable|string|max:255|exists:projects,slug',
        ];
    }
}
