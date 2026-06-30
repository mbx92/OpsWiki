<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LinksContentToProject;
use App\Models\Category;
use App\Models\Snippet;
use App\Models\Tag;
use App\Services\ActivityLogService;
use App\Services\ProjectDocumentationService;
use App\Support\TenantValidation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SnippetController extends Controller
{
    use LinksContentToProject;

    public function index(Request $request): Response
    {
        $query = Snippet::with(['category', 'tags'])->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('command', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($platform = $request->get('platform')) {
            $query->where('platform', $platform);
        }

        if ($request->boolean('favorite')) {
            $query->where('is_favorite', true);
        }

        return Inertia::render('Snippets/Index', [
            'snippets' => $query->paginate(20)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'filters' => $request->only(['q', 'platform', 'favorite']),
            'platforms' => Snippet::platformOptions(),
        ]);
    }

    public function create(Request $request): Response
    {
        $context = $this->projectCreateContext($request);
        $prefill = $context['prefill'];

        if ($request->filled('title')) {
            $prefill['title'] = (string) $request->query('title');
        }

        return Inertia::render('Snippets/Create', [
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'platforms' => Snippet::platformOptions(),
            'linkProject' => $context['linkProject'],
            'prefill' => $prefill,
        ]);
    }

    public function store(Request $request, ActivityLogService $activity): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'command' => 'required|string',
            'language' => 'required|string|max:50',
            'platform' => 'nullable|string|max:50',
            'category_id' => ['nullable', TenantValidation::exists('categories', 'id')],
            'is_tested' => 'boolean',
            'is_favorite' => 'boolean',
            'tag_names' => 'nullable|array',
            'tag_names.*' => 'string|max:50',
        ], $this->linkProjectValidationRules()));

        $snippet = Snippet::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        if (! empty($validated['tag_names'])) {
            $snippet->syncTags($validated['tag_names']);
        }

        $this->linkProjectFromRequest($request, $snippet);
        $activity->log($request->user(), 'created', $snippet);

        $linkProject = app(ProjectDocumentationService::class)->resolveFromRequest($request);
        if ($linkProject) {
            return redirect()->route('projects.show', $linkProject)->with('success', 'Snippet created and linked to project.');
        }

        return redirect()->route('snippets.index')->with('success', 'Snippet created.');
    }

    public function edit(Snippet $snippet): Response
    {
        $snippet->load('tags');

        return Inertia::render('Snippets/Edit', [
            'snippet' => $snippet,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'platforms' => Snippet::platformOptions(),
        ]);
    }

    public function update(Request $request, Snippet $snippet, ActivityLogService $activity): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'command' => 'required|string',
            'language' => 'required|string|max:50',
            'platform' => 'nullable|string|max:50',
            'category_id' => ['nullable', TenantValidation::exists('categories', 'id')],
            'is_tested' => 'boolean',
            'is_favorite' => 'boolean',
            'tag_names' => 'nullable|array',
            'tag_names.*' => 'string|max:50',
        ]);

        $snippet->update($validated);
        $snippet->syncTags($validated['tag_names'] ?? []);
        $activity->log($request->user(), 'updated', $snippet);

        return redirect()->route('snippets.index')->with('success', 'Snippet updated.');
    }

    public function destroy(Snippet $snippet, ActivityLogService $activity): RedirectResponse
    {
        $activity->log(request()->user(), 'deleted', $snippet);
        $snippet->delete();

        return redirect()->route('snippets.index')->with('success', 'Snippet deleted.');
    }

    public function toggleFavorite(Snippet $snippet): RedirectResponse
    {
        $snippet->update(['is_favorite' => ! $snippet->is_favorite]);

        return back();
    }

    public function copy(Snippet $snippet): RedirectResponse
    {
        $snippet->update(['last_used_at' => now()]);

        return back();
    }
}
