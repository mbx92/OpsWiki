<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PlatformPolicyController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Platform/Policies/Index', [
            'documents' => LegalDocument::orderBy('title')->get(),
        ]);
    }

    public function edit(LegalDocument $document): Response
    {
        return Inertia::render('Platform/Policies/Edit', [
            'document' => $document,
        ]);
    }

    public function update(Request $request, LegalDocument $document): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content_markdown' => 'required|string',
            'status' => 'required|in:draft,published',
        ]);

        $contentChanged = $document->content_markdown !== $validated['content_markdown'];

        $document->update([
            ...$validated,
            'version' => $document->version + ($contentChanged ? 1 : 0),
            'updated_by' => $request->user()->id,
        ]);

        return redirect()->route('platform.policies.index')->with('success', 'Policy updated.');
    }
}
