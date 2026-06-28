<?php

namespace App\Http\Controllers;

use App\Services\SopImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SopImportController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Sops/Import');
    }

    public function store(Request $request, SopImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,review,tested,production',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:20480',
        ]);

        $result = $importer->import(
            $request->file('files'),
            $request->user()->id,
            $validated['status'],
        );

        if ($result['imported'] === 0) {
            return back()->withErrors(['files' => 'No valid markdown files were imported.']);
        }

        $message = "Imported {$result['imported']} SOP(s).";

        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} file(s) skipped)";
        }

        $first = $result['sops'][0] ?? null;

        if ($result['imported'] === 1 && $first) {
            return redirect()->route('sops.show', $first)->with('success', $message);
        }

        return redirect()->route('sops.index')->with('success', $message);
    }
}
