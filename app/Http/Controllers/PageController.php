<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\LinksContentToProject;
use App\Models\Book;
use App\Models\Category;
use App\Models\Page;
use App\Models\PageRelation;
use App\Models\Tag;
use App\Models\PageVersion;
use App\Services\ActivityLogService;
use App\Services\MarkdownService;
use App\Services\MinioArchiveService;
use App\Services\PageVersionService;
use App\Services\PlanGateService;
use App\Services\ProjectDocumentationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    use LinksContentToProject;

    public function index(Request $request): Response
    {
        $query = Page::with(['category', 'tags', 'book'])->latest();

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('summary', 'ilike', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($scope = $request->get('scope')) {
            if ($scope === 'standalone') {
                $query->whereNull('book_id');
            } elseif ($scope === 'in_book') {
                $query->whereNotNull('book_id');
            }
        }

        return Inertia::render('Wiki/Index', [
            'pages' => $query->paginate(20)->withQueryString(),
            'categories' => Category::orderBy('sort_order')->get(['id', 'name', 'slug']),
            'filters' => $request->only(['q', 'status', 'category_id', 'scope']),
        ]);
    }

    public function create(Request $request, ProjectDocumentationService $documentation): Response
    {
        $context = $this->projectCreateContext($request);
        $prefill = $context['prefill'];
        $project = $documentation->resolveFromRequest($request);

        if ($project && $request->filled('template')) {
            $prefill = array_merge($prefill, $documentation->wikiPrefill(
                (string) $request->query('template'),
                $project,
            ));
        }

        return Inertia::render('Wiki/Create', [
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'linkProject' => $context['linkProject'],
            'prefill' => $prefill,
        ]);
    }

    public function store(Request $request, MarkdownService $markdown, ActivityLogService $activity, PlanGateService $planGate): RedirectResponse
    {
        $planGate->assertWithinLimit('pages');

        $validated = $request->validate(array_merge([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug',
            'summary' => 'nullable|string|max:500',
            'content_markdown' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,review,tested,production,deprecated,archived',
            'visibility' => 'required|in:private,internal,public',
            'tag_names' => 'nullable|array',
            'tag_names.*' => 'string|max:50',
        ], PageRelation::relatedValidationRules(), $this->linkProjectValidationRules()));

        $page = Page::create([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Page::uniqueSlug($validated['title']),
            'summary' => $validated['summary'] ?? null,
            'content_markdown' => $validated['content_markdown'] ?? null,
            'content_html' => $markdown->toHtml($validated['content_markdown'] ?? null),
            'category_id' => $validated['category_id'] ?? null,
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'published_at' => in_array($validated['status'], ['production', 'tested']) ? now() : null,
        ]);

        if (! empty($validated['tag_names'])) {
            $page->syncTags($validated['tag_names']);
        }

        if (! empty($validated['related'])) {
            PageRelation::syncFor($page, $validated['related']);
        }

        $this->linkProjectFromRequest($request, $page);

        $activity->log($request->user(), 'created', $page);

        $linkProject = app(ProjectDocumentationService::class)->resolveFromRequest($request);
        if ($linkProject) {
            return redirect()->route('projects.show', $linkProject)->with('success', 'Page created and linked to project.');
        }

        return redirect()->route('wiki.show', $page)->with('success', 'Page created.');
    }

    public function show(Page $page): Response
    {
        $page->load(['category', 'tags', 'creator', 'updater', 'assets', 'book']);

        return Inertia::render('Wiki/Show', [
            'page' => $page,
            'publicUrl' => $page->isPubliclyAccessible()
                ? route('share.pages.show', $page)
                : null,
            'related' => PageRelation::relatedItemsFor($page),
        ]);
    }

    public function edit(Page $page): Response
    {
        $page->load('tags');

        return Inertia::render('Wiki/Edit', [
            'page' => $page->load('book'),
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'books' => Book::orderBy('title')->get(['id', 'title', 'slug']),
            'tags' => Tag::orderBy('name')->get(['id', 'name']),
            'related' => PageRelation::relatedItemsFor($page),
            'linkable' => PageRelation::linkableCatalog(PageRelation::TYPE_PAGES, $page->id),
        ]);
    }

    public function update(Request $request, Page $page, MarkdownService $markdown, ActivityLogService $activity, PageVersionService $versions): RedirectResponse
    {
        $validated = $request->validate(array_merge([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:pages,slug,'.$page->id,
            'summary' => 'nullable|string|max:500',
            'content_markdown' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'book_id' => 'nullable|exists:books,id',
            'status' => 'required|in:draft,review,tested,production,deprecated,archived',
            'visibility' => 'required|in:private,internal,public',
            'tag_names' => 'nullable|array',
            'tag_names.*' => 'string|max:50',
        ], PageRelation::relatedValidationRules()));

        $content = $this->resolveContentUpdate(
            $page,
            $validated['content_markdown'] ?? null,
            $markdown,
        );

        $versions->snapshot($page, $request->user());

        $bookId = $validated['book_id'] ?? null;
        $sortOrder = (int) $page->sort_order;

        if ($bookId && (int) $page->book_id !== (int) $bookId) {
            $sortOrder = ((int) Book::find($bookId)?->pages()->max('sort_order')) + 1;
        } elseif (! $bookId) {
            $sortOrder = 0;
        }

        $page->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? Page::uniqueSlug($validated['title'], $page->id),
            'summary' => $validated['summary'] ?? null,
            'content_markdown' => $content['content_markdown'],
            'content_html' => $content['content_html'],
            'category_id' => $validated['category_id'] ?? null,
            'book_id' => $bookId,
            'sort_order' => $sortOrder,
            'status' => $validated['status'],
            'visibility' => $validated['visibility'],
            'updated_by' => $request->user()->id,
            'published_at' => in_array($validated['status'], ['production', 'tested']) && ! $page->published_at
                ? now()
                : $page->published_at,
        ]);

        $page->syncTags($validated['tag_names'] ?? []);
        PageRelation::syncFor($page, $validated['related'] ?? []);
        $activity->log($request->user(), 'updated', $page);

        return redirect()->route('wiki.show', $page)->with('success', 'Page updated.');
    }

    public function updateSharing(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'visibility' => 'required|in:private,internal,public',
        ]);

        $page->update([
            'visibility' => $validated['visibility'],
            'updated_by' => $request->user()->id,
        ]);

        $message = $page->isPublic()
            ? 'Page is now publicly shareable.'
            : 'Page sharing settings updated.';

        return back()->with('success', $message);
    }

    public function destroy(Page $page, ActivityLogService $activity): RedirectResponse
    {
        $activity->log(request()->user(), 'deleted', $page);
        $page->delete();

        return redirect()->route('wiki.index')->with('success', 'Page deleted.');
    }

    public function export(Page $page, Request $request, MinioArchiveService $archive, ActivityLogService $activity): HttpResponse
    {
        if (filled($page->content_markdown)) {
            $filename = $page->slug.'.md';
            $mimeType = 'text/markdown; charset=utf-8';
            $content = '# '.$page->title."\n\n".$page->content_markdown;
        } else {
            $filename = $page->slug.'.html';
            $mimeType = 'text/html; charset=utf-8';
            $content = '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><title>'
                .e($page->title)
                .'</title></head><body>'
                .$page->content_html
                .'</body></html>';
        }

        $archive->archiveContent(
            $content,
            $filename,
            $mimeType,
            'export',
            $page,
            $request->user()->id,
            [
                'page_slug' => $page->slug,
                'page_title' => $page->title,
            ],
        );

        $activity->log($request->user(), 'exported', $page);

        return response($content, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array{content_markdown: ?string, content_html: ?string}
     */
    private function resolveContentUpdate(Page $page, ?string $markdownContent, MarkdownService $markdown): array
    {
        $markdownContent = $markdownContent !== null ? trim($markdownContent) : null;
        $isHtmlOnly = filled($page->content_html) && blank($page->content_markdown);

        if (filled($markdownContent)) {
            return [
                'content_markdown' => $markdownContent,
                'content_html' => $markdown->toHtml($markdownContent),
            ];
        }

        if ($isHtmlOnly) {
            return [
                'content_markdown' => null,
                'content_html' => $page->content_html,
            ];
        }

        if (filled($page->content_markdown)) {
            return [
                'content_markdown' => null,
                'content_html' => null,
            ];
        }

        return [
            'content_markdown' => $page->content_markdown,
            'content_html' => $page->content_html,
        ];
    }

    public function history(Page $page): Response
    {
        return Inertia::render('Wiki/History', [
            'page' => $page->only(['id', 'title', 'slug']),
            'versions' => $page->versions()->with('author')->latest('version_number')->get(),
        ]);
    }

    public function showVersion(Page $page, PageVersion $version): Response
    {
        abort_unless((int) $version->page_id === (int) $page->id, 404);

        return Inertia::render('Wiki/VersionShow', [
            'page' => $page->only(['id', 'title', 'slug']),
            'version' => $version->load('author'),
        ]);
    }

    public function restore(Page $page, PageVersion $version, Request $request, PageVersionService $versions, MarkdownService $markdown, ActivityLogService $activity): RedirectResponse
    {
        abort_unless((int) $version->page_id === (int) $page->id, 404);

        $versions->restore($page, $version, $request->user(), $markdown);
        $activity->log($request->user(), 'restored', $page, 'Version '.$version->version_number);

        return redirect()->route('wiki.show', $page)->with('success', 'Page restored to version '.$version->version_number.'.');
    }
}
