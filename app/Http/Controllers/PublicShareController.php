<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicShareController extends Controller
{
    public function showBook(Book $book): Response
    {
        if (! $book->isPublic()) {
            throw new NotFoundHttpException;
        }

        $book->load(['pages' => fn ($q) => $q->orderBy('sort_order'), 'category']);

        return Inertia::render('Public/Book', [
            'book' => $book,
        ]);
    }

    public function showPage(Page $page): Response
    {
        $page->load(['category', 'tags', 'book']);

        if (! $this->isPagePubliclyAccessible($page)) {
            throw new NotFoundHttpException;
        }

        return Inertia::render('Public/Page', [
            'page' => $page,
        ]);
    }

    private function isPagePubliclyAccessible(Page $page): bool
    {
        if ($page->isPublic()) {
            return true;
        }

        return $page->book?->isPublic() ?? false;
    }
}
