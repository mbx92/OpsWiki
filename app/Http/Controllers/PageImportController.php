<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Services\MinioArchiveService;
use App\Services\PageImportService;
use App\Services\PlanGateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PageImportController extends Controller
{
    public function create(Request $request): Response
    {
        $book = null;

        if ($request->filled('book')) {
            $book = Book::query()
                ->where('slug', $request->string('book'))
                ->first(['id', 'title', 'slug', 'description', 'category_id']);
        }

        return Inertia::render('Wiki/Import', [
            'categories' => Category::orderBy('sort_order')->get(['id', 'name']),
            'book' => $book,
        ]);
    }

    public function store(Request $request, PageImportService $importer, MinioArchiveService $archive, PlanGateService $planGate): RedirectResponse
    {
        $validated = $request->validate([
            'book_id' => 'nullable|exists:books,id',
            'book_title' => 'required_without:book_id|nullable|string|max:255',
            'book_description' => 'nullable|string|max:1000',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'required|in:draft,review,tested,production',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:20480',
        ]);

        $planGate->assertWithinLimit('pages', count($validated['files']));

        $book = isset($validated['book_id'])
            ? Book::findOrFail($validated['book_id'])
            : null;

        $uploadedFiles = $request->file('files');

        $result = $importer->import(
            bookTitle: $book?->title ?? $validated['book_title'],
            files: $uploadedFiles,
            userId: $request->user()->id,
            categoryId: $validated['category_id'] ?? null,
            bookDescription: $validated['book_description'] ?? null,
            status: $validated['status'],
            book: $book,
        );

        foreach ($uploadedFiles as $file) {
            $archive->archiveUploadedFile(
                $file,
                'import',
                $result['book'],
                $request->user()->id,
                [
                    'book_id' => $result['book']->id,
                    'book_slug' => $result['book']->slug,
                    'imported_pages' => $result['imported'],
                ],
            );
        }

        $message = $book
            ? "Added {$result['imported']} page(s) to \"{$result['book']->title}\""
            : "Imported {$result['imported']} page(s) into book \"{$result['book']->title}\"";

        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} file(s) skipped)";
        }

        return redirect()
            ->route('books.show', $result['book'])
            ->with('success', $message);
    }
}
