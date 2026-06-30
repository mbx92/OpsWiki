<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Page;
use App\Services\ActivityLogService;
use App\Support\TenantValidation;
use App\Services\MinioArchiveService;
use App\Services\PageImportService;
use App\Services\StaticExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use ZipArchive;

class BookController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Books/Index', [
            'books' => Book::withCount('pages')
                ->with('category')
                ->latest()
                ->get(),
        ]);
    }

    public function show(Request $request, Book $book): Response
    {
        $book->load(['pages' => fn ($q) => $q->orderBy('sort_order'), 'category', 'creator']);

        return Inertia::render('Books/Show', [
            'book' => $book,
            'publicUrl' => $book->isPublic() ? route('share.books.show', $book) : null,
            'availablePages' => Page::query()
                ->whereNull('book_id')
                ->orderBy('title')
                ->get(['id', 'title', 'slug', 'status']),
            'canManage' => $request->user()?->hasPermission('books.manage') ?? false,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Books/Create', [
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validatedBook($request);

        $book = Book::create([
            ...$validated,
            'slug' => Book::uniqueSlug($validated['title']),
            'created_by' => $request->user()->id,
        ]);

        $activity->log($request->user(), 'created', $book);

        return redirect()->route('books.show', $book)->with('success', 'Book created.');
    }

    public function edit(Book $book): Response
    {
        $book->load('category');

        return Inertia::render('Books/Edit', [
            'book' => $book,
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Book $book, ActivityLogService $activity): RedirectResponse
    {
        $validated = $this->validatedBook($request);

        $book->update([
            ...$validated,
            'slug' => Book::uniqueSlug($validated['title'], $book->id),
        ]);

        $activity->log($request->user(), 'updated', $book);

        return redirect()->route('books.show', $book)->with('success', 'Book updated.');
    }

    public function attachPages(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'page_ids' => 'required|array|min:1',
            'page_ids.*' => 'integer|exists:pages,id',
        ]);

        $sortOrder = ((int) $book->pages()->max('sort_order')) + 1;
        $attached = 0;

        foreach ($validated['page_ids'] as $pageId) {
            $updated = Page::query()
                ->whereKey($pageId)
                ->whereNull('book_id')
                ->update([
                    'book_id' => $book->id,
                    'sort_order' => $sortOrder++,
                    'category_id' => $book->category_id,
                ]);

            $attached += $updated;
        }

        if ($attached === 0) {
            return back()->withErrors(['page_ids' => 'No standalone pages were added.']);
        }

        return back()->with('success', "{$attached} page(s) added to book.");
    }

    public function movePage(Request $request, Book $book, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        abort_unless((int) $page->book_id === (int) $book->id, 404);

        $pages = $book->pages()->orderBy('sort_order')->orderBy('id')->get();
        $index = $pages->search(fn (Page $p) => $p->id === $page->id);

        if ($index === false) {
            return back();
        }

        $swapIndex = $validated['direction'] === 'up' ? $index - 1 : $index + 1;

        if ($swapIndex < 0 || $swapIndex >= $pages->count()) {
            return back();
        }

        $other = $pages[$swapIndex];
        $currentOrder = (int) $page->sort_order;
        $otherOrder = (int) $other->sort_order;

        $page->update(['sort_order' => $otherOrder]);
        $other->update(['sort_order' => $currentOrder]);

        return back()->with('success', 'Page order updated.');
    }

    public function updateSharing(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'visibility' => 'required|in:private,internal,public',
            'make_pages_public' => 'boolean',
        ]);

        $book->update(['visibility' => $validated['visibility']]);

        if ($validated['make_pages_public'] ?? false) {
            $book->pages()->update(['visibility' => 'public']);
        }

        $message = $book->isPublic()
            ? 'Book is now publicly shareable.'
            : 'Book sharing settings updated.';

        return back()->with('success', $message);
    }

    public function destroy(Book $book, ActivityLogService $activity): RedirectResponse
    {
        $activity->log(request()->user(), 'deleted', $book);
        $book->pages()->update(['book_id' => null]);
        $book->delete();

        return redirect()->route('books.index')->with('success', 'Book removed from shelf. Pages kept in wiki.');
    }

    public function export(Book $book, Request $request, MinioArchiveService $archive, ActivityLogService $activity): HttpResponse
    {
        $book->load(['pages' => fn ($q) => $q->orderBy('sort_order')]);

        $zipPath = tempnam(sys_get_temp_dir(), 'book_export_');
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($book->pages as $page) {
            $content = filled($page->content_markdown)
                ? '# '.$page->title."\n\n".$page->content_markdown
                : '# '.$page->title."\n\n".strip_tags($page->content_html ?? '');

            $zip->addFromString(sprintf('%02d-%s.md', $page->sort_order ?: 0, $page->slug), $content);
        }

        $zip->close();
        $content = file_get_contents($zipPath);
        unlink($zipPath);

        $filename = $book->slug.'.zip';

        $archive->archiveContent(
            $content,
            $filename,
            'application/zip',
            'export',
            $book,
            $request->user()->id,
            ['book_slug' => $book->slug, 'page_count' => $book->pages->count()],
        );

        $activity->log($request->user(), 'exported', $book);

        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportStatic(Book $book, StaticExportService $exporter, ActivityLogService $activity): HttpResponse
    {
        $content = $exporter->exportBook($book);
        $filename = $book->slug.'-static.zip';

        $activity->log(request()->user(), 'exported', $book, 'Static site');

        return response($content, 200, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * @return array{title: string, description: ?string, category_id: ?int, status: string}
     */
    private function validatedBook(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category_id' => ['nullable', TenantValidation::exists('categories', 'id')],
            'status' => 'required|in:draft,review,tested,production,deprecated,archived',
        ]);
    }
}
