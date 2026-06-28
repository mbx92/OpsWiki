<?php

namespace App\Http\Controllers;

use App\Services\KnowledgeGraphService;
use Inertia\Inertia;
use Inertia\Response;

class KnowledgeGraphController extends Controller
{
    public function index(KnowledgeGraphService $graph): Response
    {
        return Inertia::render('KnowledgeGraph/Index', $graph->build());
    }
}
