<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LinksContentToProject;
use App\Models\PageRelation;
use App\Models\Sop;
use App\Models\Tag;
use App\Services\ActivityLogService;
use App\Services\ProjectDocumentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SopController extends Controller
{
    use LinksContentToProject;

    public function index(Request $request): Response
    {
        $query = Sop::with('tags')->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('purpose', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        return Inertia::render('Sops/Index', [
            'sops' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'status']),
            'statuses' => ['draft', 'review', 'tested', 'production', 'deprecated', 'archived'],
        ]);
    }

    public function create(Request $request): Response
    {
        $context = $this->projectCreateContext($request);
        $prefill = $context['prefill'];

        if ($request->filled('title')) {
            $prefill['title'] = (string) $request->query('title');
        }

        return Inertia::render('Sops/Create', [
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'statuses' => ['draft', 'review', 'tested', 'production', 'deprecated', 'archived'],
            'linkProject' => $context['linkProject'],
            'prefill' => $prefill,
        ]);
    }

    public function store(Request $request, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validated($request);

        $sop = Sop::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $sop->syncTags($validated['tag_names'] ?? []);
        $this->linkProjectFromRequest($request, $sop);
        $activity->log($request->user(), 'created', $sop);

        $linkProject = app(ProjectDocumentationService::class)->resolveFromRequest($request);
        if ($linkProject) {
            return redirect()->route('projects.show', $linkProject)->with('success', 'SOP created and linked to project.');
        }

        return redirect()->route('sops.show', $sop)->with('success', 'SOP created.');
    }

    public function show(Sop $sop): Response
    {
        $sop->load(['tags', 'creator', 'updater']);

        return Inertia::render('Sops/Show', [
            'sop' => $sop,
            'related' => \App\Models\PageRelation::relatedItemsFor($sop),
        ]);
    }

    public function edit(Sop $sop): Response
    {
        $sop->load('tags');

        return Inertia::render('Sops/Edit', [
            'sop' => $sop,
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'statuses' => ['draft', 'review', 'tested', 'production', 'deprecated', 'archived'],
            'related' => PageRelation::relatedItemsFor($sop),
            'linkable' => PageRelation::linkableCatalog(PageRelation::TYPE_SOPS, $sop->id),
        ]);
    }

    public function update(Request $request, Sop $sop, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validated($request, $sop);

        $sop->update([
            ...$validated,
            'slug' => $validated['slug'] ?? Sop::uniqueSlug($validated['title'], $sop->id),
            'updated_by' => $request->user()->id,
        ]);

        $sop->syncTags($validated['tag_names'] ?? []);
        PageRelation::syncFor($sop, $validated['related'] ?? []);
        $activity->log($request->user(), 'updated', $sop);

        return redirect()->route('sops.show', $sop)->with('success', 'SOP updated.');
    }

    public function destroy(Sop $sop, ActivityLogService $activity): RedirectResponse
    {
        $activity->log(request()->user(), 'deleted', $sop);
        $sop->delete();

        return redirect()->route('sops.index')->with('success', 'SOP deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Sop $sop = null): array
    {
        return $request->validate(array_merge([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sops,slug'.($sop ? ','.$sop->id : ''),
            'purpose' => 'nullable|string',
            'use_case' => 'nullable|string',
            'requirements' => 'nullable|string',
            'steps' => 'nullable|string',
            'validation' => 'nullable|string',
            'rollback' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:draft,review,tested,production,deprecated,archived',
            'tag_names' => 'nullable|array',
            'tag_names.*' => 'string|max:50',
        ], PageRelation::relatedValidationRules(), $this->linkProjectValidationRules()));
    }
}
