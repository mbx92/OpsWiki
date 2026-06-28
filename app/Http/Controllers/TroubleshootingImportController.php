<?php

namespace App\Http\Controllers;

use App\Services\TroubleshootingImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TroubleshootingImportController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Troubleshooting/Import');
    }

    public function store(Request $request, TroubleshootingImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:open,investigating,solved,workaround,failed',
            'severity' => 'required|in:low,medium,high,critical',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|max:20480',
        ]);

        $result = $importer->import(
            $request->file('files'),
            $request->user()->id,
            $validated['status'],
            $validated['severity'],
        );

        if ($result['imported'] === 0) {
            return back()->withErrors(['files' => 'No valid markdown files were imported.']);
        }

        $message = "Imported {$result['imported']} case(s).";

        if ($result['skipped'] > 0) {
            $message .= " ({$result['skipped']} file(s) skipped)";
        }

        $first = $result['cases'][0] ?? null;

        if ($result['imported'] === 1 && $first) {
            return redirect()->route('troubleshooting.show', $first)->with('success', $message);
        }

        return redirect()->route('troubleshooting.index')->with('success', $message);
    }
}
