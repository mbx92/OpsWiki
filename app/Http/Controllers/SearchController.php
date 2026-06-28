<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request, SearchService $search): Response
    {
        $q = trim($request->get('q', ''));
        $smart = $request->boolean('smart', true);
        $results = $search->search($q, smart: $smart);

        return Inertia::render('Search/Index', [
            'query' => $q,
            'pages' => $results['pages'],
            'snippets' => $results['snippets'],
            'inbox' => $results['inbox'],
            'sops' => $results['sops'],
            'troubleshooting' => $results['troubleshooting'],
            'projects' => $results['projects'],
            'mode' => $results['mode'],
        ]);
    }

    public function query(Request $request, SearchService $search): JsonResponse
    {
        $q = trim($request->get('q', ''));
        $smart = $request->boolean('smart', true);

        return response()->json([
            'query' => $q,
            ...$search->search($q, 5, smart: $smart),
        ]);
    }
}
