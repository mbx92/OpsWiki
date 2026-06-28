<?php

namespace App\Http\Controllers;

use App\Models\PageRelation;
use App\Models\Project;
use App\Models\Tag;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Project::with('tags')->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('Projects/Index', [
            'projects' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status']),
            'statuses' => ['planning', 'development', 'staging', 'production', 'maintenance', 'archived'],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Projects/Create', [
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'statuses' => ['planning', 'development', 'staging', 'production', 'maintenance', 'archived'],
        ]);
    }

    public function store(Request $request, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validated($request);

        $project = Project::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        $project->syncTags($validated['tag_names'] ?? []);
        $activity->log($request->user(), 'created', $project);

        return redirect()->route('projects.show', $project)->with('success', 'Project created.');
    }

    public function show(Project $project): Response
    {
        $project->load(['tags', 'creator']);

        return Inertia::render('Projects/Show', [
            'project' => $project,
            'related' => \App\Models\PageRelation::relatedItemsFor($project),
        ]);
    }

    public function edit(Project $project): Response
    {
        $project->load('tags');

        return Inertia::render('Projects/Edit', [
            'project' => $project,
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'statuses' => ['planning', 'development', 'staging', 'production', 'maintenance', 'archived'],
            'related' => PageRelation::relatedItemsFor($project),
            'linkable' => PageRelation::linkableCatalog(PageRelation::TYPE_PROJECTS, $project->id),
        ]);
    }

    public function update(Request $request, Project $project, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validated($request, $project);

        $project->update([
            ...$validated,
            'slug' => $validated['slug'] ?? Project::uniqueSlug($validated['name'], $project->id),
        ]);

        $project->syncTags($validated['tag_names'] ?? []);
        PageRelation::syncFor($project, $validated['related'] ?? []);
        $activity->log($request->user(), 'updated', $project);

        return redirect()->route('projects.show', $project)->with('success', 'Project updated.');
    }

    public function destroy(Project $project, ActivityLogService $activity): RedirectResponse
    {
        $activity->log(request()->user(), 'deleted', $project);
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Project $project = null): array
    {
        return $request->validate(array_merge([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:projects,slug'.($project ? ','.$project->id : ''),
            'description' => 'nullable|string',
            'status' => 'required|in:planning,development,staging,production,maintenance,archived',
            'repository_url' => 'nullable|url|max:500',
            'production_url' => 'nullable|url|max:500',
            'staging_url' => 'nullable|url|max:500',
            'server_location' => 'nullable|string|max:255',
            'environment_notes' => 'nullable|string',
            'tag_names' => 'nullable|array',
            'tag_names.*' => 'string|max:50',
        ], PageRelation::relatedValidationRules()));
    }
}
