<?php

namespace App\Services;

use App\Models\PageRelation;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ProjectDocumentationService
{
    /** @var array<string, string> */
    private const QUICK_CREATE_PERMISSIONS = [
        'wiki-overview' => 'wiki.create',
        'wiki-deploy' => 'wiki.create',
        'sop-deploy' => 'sops.manage',
        'snippet' => 'snippets.manage',
        'troubleshooting' => 'troubleshooting.manage',
    ];

    public function resolveFromRequest(Request $request): ?Project
    {
        $slug = trim((string) $request->input('link_project', $request->query('link_project', '')));

        if ($slug === '') {
            return null;
        }

        return Project::where('slug', $slug)->first();
    }

    public function linkToProject(Project $project, Model $content): void
    {
        PageRelation::linkTo($project, $content);
    }

    /**
     * @return list<array{key: string, label: string, route: string, params: array<string, string>}>
     */
    public function quickCreateActions(Project $project, ?User $user = null): array
    {
        $base = ['link_project' => $project->slug];
        $name = $project->name;

        $actions = [
            [
                'key' => 'wiki-overview',
                'label' => 'Wiki — Overview',
                'route' => 'wiki.create',
                'params' => array_merge($base, [
                    'title' => "{$name} — Overview",
                    'template' => 'overview',
                ]),
            ],
            [
                'key' => 'wiki-deploy',
                'label' => 'Wiki — Deployment',
                'route' => 'wiki.create',
                'params' => array_merge($base, [
                    'title' => "{$name} — Deployment",
                    'template' => 'deploy',
                ]),
            ],
            [
                'key' => 'sop-deploy',
                'label' => 'SOP — Deploy',
                'route' => 'sops.create',
                'params' => array_merge($base, [
                    'title' => "{$name} — Deploy procedure",
                ]),
            ],
            [
                'key' => 'snippet',
                'label' => 'Snippet',
                'route' => 'snippets.create',
                'params' => array_merge($base, [
                    'title' => "{$name} — ",
                ]),
            ],
            [
                'key' => 'troubleshooting',
                'label' => 'Troubleshooting case',
                'route' => 'troubleshooting.create',
                'params' => array_merge($base, [
                    'title' => "{$name} — Incident",
                ]),
            ],
        ];

        return array_values(array_filter($actions, function (array $action) use ($user) {
            if (! $this->routeExists($action['route'])) {
                return false;
            }

            if ($user === null) {
                return true;
            }

            $permission = self::QUICK_CREATE_PERMISSIONS[$action['key']] ?? null;

            return $permission === null || $user->hasPermission($permission);
        }));
    }

    /**
     * @return array<string, mixed>
     */
    public function showPayload(Project $project, ?User $user = null): array
    {
        $grouped = PageRelation::relatedItemsGroupedFor($project);
        $stats = $this->documentationStats($grouped);

        return [
            'documentation' => [
                'groups' => $grouped,
                'stats' => $stats,
                'total' => array_sum($stats),
            ],
            'quickCreate' => $this->quickCreateActions($project, $user),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function wikiPrefill(?string $template, Project $project): array
    {
        $name = $project->name;

        return match ($template) {
            'deploy' => [
                'title' => "{$name} — Deployment",
                'summary' => "Deployment guide for {$name}.",
                'content_markdown' => "## Prerequisites\n\n- \n\n## Deploy steps\n\n1. \n\n## Rollback\n\n- \n\n## Verification\n\n- ",
            ],
            'env' => [
                'title' => "{$name} — Environment",
                'summary' => "Environment and configuration for {$name}.",
                'content_markdown' => "## Environments\n\n| Env | URL | Notes |\n|-----|-----|-------|\n| Production | | |\n| Staging | | |\n\n## Variables\n\n```env\n# Example\n```",
            ],
            'runbook' => [
                'title' => "{$name} — Runbook",
                'summary' => "Operational runbook for {$name}.",
                'content_markdown' => "## Common tasks\n\n### Restart service\n\n```bash\n# \n```\n\n## Monitoring\n\n- \n\n## Contacts\n\n- ",
            ],
            default => [
                'title' => "{$name} — Overview",
                'summary' => "Architecture and overview for {$name}.",
                'content_markdown' => "## Overview\n\n\n\n## Architecture\n\n\n\n## Related links\n\n- Repository: ".($project->repository_url ?: '—')."\n- Production: ".($project->production_url ?: '—')."\n",
            ],
        };
    }

    /**
     * @param  list<array{type: string, label: string, items: list<mixed>}>  $grouped
     * @return array<string, int>
     */
    private function documentationStats(array $grouped): array
    {
        $stats = [
            'pages' => 0,
            'sops' => 0,
            'troubleshooting_cases' => 0,
            'snippets' => 0,
            'tools' => 0,
        ];

        foreach ($grouped as $group) {
            $type = $group['type'];
            if (array_key_exists($type, $stats)) {
                $stats[$type] = count($group['items']);
            }
        }

        return $stats;
    }

    private function routeExists(string $name): bool
    {
        return app('router')->has($name);
    }
}
