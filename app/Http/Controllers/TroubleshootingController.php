<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LinksContentToProject;
use App\Models\PageRelation;
use App\Models\Tag;
use App\Models\TroubleshootingCase;
use App\Services\ActivityLogService;
use App\Services\ProjectDocumentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TroubleshootingController extends Controller
{
    use LinksContentToProject;

    public function index(Request $request): Response
    {
        $query = TroubleshootingCase::with('tags')->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('symptoms', 'ilike', "%{$search}%")
                    ->orWhere('working_solution', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($severity = $request->get('severity')) {
            $query->where('severity', $severity);
        }

        return Inertia::render('Troubleshooting/Index', [
            'cases' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status', 'severity']),
            'statuses' => ['open', 'investigating', 'solved', 'workaround', 'failed', 'archived'],
            'severities' => ['low', 'medium', 'high', 'critical'],
        ]);
    }

    public function create(Request $request): Response
    {
        $context = $this->projectCreateContext($request);
        $prefill = $context['prefill'];

        if ($request->filled('title')) {
            $prefill['title'] = (string) $request->query('title');
        }

        return Inertia::render('Troubleshooting/Create', [
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'statuses' => ['open', 'investigating', 'solved', 'workaround', 'failed', 'archived'],
            'severities' => ['low', 'medium', 'high', 'critical'],
            'linkProject' => $context['linkProject'],
            'prefill' => $prefill,
        ]);
    }

    public function store(Request $request, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validated($request);

        $case = TroubleshootingCase::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $case->syncTags($validated['tag_names'] ?? []);
        $this->linkProjectFromRequest($request, $case);
        $activity->log($request->user(), 'created', $case);

        $linkProject = app(ProjectDocumentationService::class)->resolveFromRequest($request);
        if ($linkProject) {
            return redirect()->route('projects.show', $linkProject)->with('success', 'Case created and linked to project.');
        }

        return redirect()->route('troubleshooting.show', $case)->with('success', 'Case created.');
    }

    public function show(TroubleshootingCase $troubleshooting): Response
    {
        $troubleshooting->load(['tags', 'creator', 'updater']);

        return Inertia::render('Troubleshooting/Show', [
            'troubleshootingCase' => $troubleshooting,
            'related' => \App\Models\PageRelation::relatedItemsFor($troubleshooting),
        ]);
    }

    public function edit(TroubleshootingCase $troubleshooting): Response
    {
        $troubleshooting->load('tags');

        return Inertia::render('Troubleshooting/Edit', [
            'troubleshootingCase' => $troubleshooting,
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'statuses' => ['open', 'investigating', 'solved', 'workaround', 'failed', 'archived'],
            'severities' => ['low', 'medium', 'high', 'critical'],
            'related' => PageRelation::relatedItemsFor($troubleshooting),
            'linkable' => PageRelation::linkableCatalog(PageRelation::TYPE_TROUBLESHOOTING, $troubleshooting->id),
        ]);
    }

    public function update(Request $request, TroubleshootingCase $troubleshooting, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validated($request, $troubleshooting);

        $troubleshooting->update([
            ...$validated,
            'slug' => $validated['slug'] ?? TroubleshootingCase::uniqueSlug($validated['title'], $troubleshooting->id),
            'updated_by' => $request->user()->id,
        ]);

        $troubleshooting->syncTags($validated['tag_names'] ?? []);
        PageRelation::syncFor($troubleshooting, $validated['related'] ?? []);
        $activity->log($request->user(), 'updated', $troubleshooting);

        return redirect()->route('troubleshooting.show', $troubleshooting)->with('success', 'Case updated.');
    }

    public function destroy(TroubleshootingCase $troubleshooting, ActivityLogService $activity): RedirectResponse
    {
        $activity->log(request()->user(), 'deleted', $troubleshooting);
        $troubleshooting->delete();

        return redirect()->route('troubleshooting.index')->with('success', 'Case deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?TroubleshootingCase $case = null): array
    {
        return $request->validate(array_merge([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:troubleshooting_cases,slug'.($case ? ','.$case->id : ''),
            'symptoms' => 'nullable|string',
            'environment' => 'nullable|string',
            'error_log' => 'nullable|string',
            'suspected_causes' => 'nullable|string',
            'diagnosis_steps' => 'nullable|string',
            'working_solution' => 'nullable|string',
            'failed_attempts' => 'nullable|string',
            'validation' => 'nullable|string',
            'prevention' => 'nullable|string',
            'severity' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,investigating,solved,workaround,failed,archived',
            'tag_names' => 'nullable|array',
            'tag_names.*' => 'string|max:50',
        ], PageRelation::relatedValidationRules(), $this->linkProjectValidationRules()));
    }
}
