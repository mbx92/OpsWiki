<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LegalController extends Controller
{
    public function show(string $slug): Response
    {
        $document = LegalDocument::where('slug', $slug)->first();

        if (! $document?->isPublished()) {
            throw new NotFoundHttpException;
        }

        return Inertia::render('Public/Legal', [
            'document' => $document,
        ]);
    }
}
