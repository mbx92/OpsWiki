<?php

namespace App\Http\Controllers;

use App\Models\InboxItem;
use App\Models\Page;
use App\Models\Snippet;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'inbox_new' => InboxItem::where('status', 'new')->count(),
                'pages_total' => Page::count(),
                'snippets_total' => Snippet::count(),
                'snippets_favorite' => Snippet::where('is_favorite', true)->count(),
            ],
            'recentPages' => Page::with('category')
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'slug', 'status', 'category_id', 'updated_at']),
            'recentInbox' => InboxItem::where('status', 'new')
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'type', 'priority', 'status', 'created_at']),
            'favoriteSnippets' => Snippet::where('is_favorite', true)
                ->latest()
                ->limit(5)
                ->get(['id', 'title', 'platform', 'command']),
        ]);
    }
}
