<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Page;
use App\Models\Tenant;
use App\Services\TenantResolver;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicPortalController extends Controller
{
    public function index(Request $request, TenantResolver $resolver): Response
    {
        $tenant = TenantContext::get() ?? $resolver->defaultTenant();

        if (! $tenant) {
            throw new NotFoundHttpException('Workspace not found.');
        }

        TenantContext::set($tenant);

        return $this->renderPortal($request, $tenant);
    }

    public function indexForTenant(Request $request, Tenant $tenant): Response
    {
        TenantContext::set($tenant);

        return $this->renderPortal($request, $tenant);
    }

    private function renderPortal(Request $request, Tenant $tenant): Response
    {
        $search = trim((string) $request->get('q'));

        $booksQuery = Book::query()
            ->where('visibility', 'public')
            ->with(['pages' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('title');

        $pagesQuery = Page::query()
            ->where('visibility', 'public')
            ->whereNull('book_id')
            ->orderBy('title');

        if ($search !== '') {
            $booksQuery->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('description', 'ilike', "%{$search}%");
            });

            $pagesQuery->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('summary', 'ilike', "%{$search}%");
            });
        }

        $books = $booksQuery->get();
        $pages = $pagesQuery->get(['id', 'title', 'slug', 'summary']);

        return Inertia::render('Public/Portal', [
            'tenant' => $tenant->only(['id', 'name', 'slug']),
            'books' => $books,
            'pages' => $pages,
            'filters' => ['q' => $search],
            'stats' => [
                'books' => $books->count(),
                'pages' => $pages->count(),
            ],
        ]);
    }
}
