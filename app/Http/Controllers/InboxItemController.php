<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\InboxItem;
use App\Models\Page;
use App\Models\Sop;
use App\Models\Snippet;
use App\Models\TroubleshootingCase;
use App\Services\MarkdownService;
use App\Services\SopImportService;
use App\Services\TroubleshootingImportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InboxItemController extends Controller
{
    public function index(Request $request): Response
    {
        $query = InboxItem::query()->latest();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        return Inertia::render('Inbox/Index', [
            'items' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['status', 'type']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Inbox/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|in:idea,error_log,command,link,draft_sop,draft_documentation,temporary_note',
            'source' => 'nullable|string|max:255',
            'priority' => 'required|in:low,normal,high,urgent',
            'tags' => 'nullable|array',
        ]);

        $item = InboxItem::create([
            ...$validated,
            'status' => 'new',
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('inbox.show', $item)->with('success', 'Inbox item created.');
    }

    public function show(InboxItem $inbox): Response
    {
        return Inertia::render('Inbox/Show', [
            'item' => $inbox->load('creator'),
        ]);
    }

    public function edit(InboxItem $inbox): Response
    {
        return Inertia::render('Inbox/Edit', [
            'item' => $inbox,
        ]);
    }

    public function update(Request $request, InboxItem $inbox): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'type' => 'required|in:idea,error_log,command,link,draft_sop,draft_documentation,temporary_note',
            'source' => 'nullable|string|max:255',
            'priority' => 'required|in:low,normal,high,urgent',
            'tags' => 'nullable|array',
            'status' => 'required|in:new,reviewed,converted,archived',
        ]);

        $inbox->update($validated);

        return redirect()->route('inbox.show', $inbox)->with('success', 'Inbox item updated.');
    }

    public function destroy(InboxItem $inbox): RedirectResponse
    {
        $inbox->delete();

        return redirect()->route('inbox.index')->with('success', 'Inbox item deleted.');
    }

    public function convertToPage(Request $request, InboxItem $inbox, MarkdownService $markdown): RedirectResponse
    {
        $page = Page::create([
            'title' => $inbox->title,
            'summary' => \Illuminate\Support\Str::limit($inbox->content ?? '', 200),
            'content_markdown' => $inbox->content,
            'content_html' => $markdown->toHtml($inbox->content),
            'status' => 'draft',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $inbox->update(['status' => 'converted']);

        return redirect()->route('wiki.edit', $page)->with('success', 'Converted to wiki page.');
    }

    public function convertToSnippet(Request $request, InboxItem $inbox): RedirectResponse
    {
        $snippet = Snippet::create([
            'title' => $inbox->title,
            'description' => $inbox->source,
            'command' => $inbox->content ?? '',
            'language' => 'bash',
            'platform' => $inbox->type === 'command' ? 'linux' : null,
            'created_by' => $request->user()->id,
        ]);

        $inbox->update(['status' => 'converted']);

        return redirect()->route('snippets.edit', $snippet)->with('success', 'Converted to snippet.');
    }

    public function convertToSop(Request $request, InboxItem $inbox, SopImportService $import): RedirectResponse
    {
        $parsed = $import->parseContent($inbox->content ?? '', $inbox->title);
        $sections = $parsed['sections'];

        $sop = Sop::create([
            'title' => $parsed['title'],
            'slug' => Sop::uniqueSlug($parsed['title']),
            'purpose' => $sections['purpose'] ?? null,
            'use_case' => $sections['use_case'] ?? null,
            'requirements' => $sections['requirements'] ?? null,
            'steps' => $sections['steps'] ?? ($inbox->content ?: null),
            'validation' => $sections['validation'] ?? null,
            'rollback' => $sections['rollback'] ?? null,
            'notes' => $sections['notes'] ?? null,
            'status' => 'draft',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $inbox->update(['status' => 'converted']);

        return redirect()->route('sops.edit', $sop)->with('success', 'Converted to SOP.');
    }

    public function convertToTroubleshooting(Request $request, InboxItem $inbox, TroubleshootingImportService $import): RedirectResponse
    {
        $parsed = $import->parseContent($inbox->content ?? '', $inbox->title);
        $sections = $parsed['sections'];
        $severity = $inbox->priority === 'urgent' ? 'critical' : ($inbox->priority === 'high' ? 'high' : 'medium');

        $case = TroubleshootingCase::create([
            'title' => $parsed['title'],
            'slug' => TroubleshootingCase::uniqueSlug($parsed['title']),
            'symptoms' => $sections['symptoms'] ?? \Illuminate\Support\Str::limit($inbox->content ?? '', 500),
            'environment' => $sections['environment'] ?? $inbox->source,
            'error_log' => $sections['error_log'] ?? ($inbox->type === 'error_log' ? $inbox->content : null),
            'suspected_causes' => $sections['suspected_causes'] ?? null,
            'diagnosis_steps' => $sections['diagnosis_steps'] ?? null,
            'working_solution' => $sections['working_solution'] ?? null,
            'failed_attempts' => $sections['failed_attempts'] ?? null,
            'validation' => $sections['validation'] ?? null,
            'prevention' => $sections['prevention'] ?? null,
            'severity' => $severity,
            'status' => 'open',
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        $inbox->update(['status' => 'converted']);

        return redirect()->route('troubleshooting.edit', $case)->with('success', 'Converted to troubleshooting case.');
    }
}
