<?php

namespace App\Http\Controllers;

use App\Models\Snippet;
use App\Models\Tool;
use App\Services\ActivityLogService;
use App\Services\PlanGateService;
use App\Support\PlanFeatureCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ToolController extends Controller
{
    public function index(PlanGateService $planGate): Response
    {
        $tools = Tool::where('status', 'active')
            ->orderBy('title')
            ->get()
            ->map(fn (Tool $tool) => [
                'id' => $tool->id,
                'title' => $tool->title,
                'slug' => $tool->slug,
                'description' => $tool->description,
                'tool_type' => $tool->tool_type,
                'locked' => ! $planGate->hasTool($tool->slug),
                'required_plan' => PlanFeatureCatalog::requiredPlanForTool($tool->slug),
            ]);

        return Inertia::render('Tools/Index', [
            'tools' => $tools,
        ]);
    }

    public function show(Tool $tool, PlanGateService $planGate): Response
    {
        $planGate->assertTool($tool->slug);

        $componentMap = [
            'minio-iam-generator' => 'Tools/MinioIamGenerator',
            'pg-restore-helper' => 'Tools/PgRestoreHelper',
            'docker-compose-builder' => 'Tools/DockerComposeBuilder',
            'rclone-copy-builder' => 'Tools/RcloneCopyBuilder',
        ];

        $component = $componentMap[$tool->slug] ?? null;

        if (! $component) {
            abort(404);
        }

        return Inertia::render($component, [
            'tool' => $tool,
        ]);
    }

    public function saveSnippet(Request $request, Tool $tool, ActivityLogService $activity, PlanGateService $planGate): RedirectResponse
    {
        $planGate->assertTool($tool->slug);

        abort_unless($tool->slug === 'minio-iam-generator', 404);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'command' => 'required|string',
            'description' => 'nullable|string|max:500',
        ]);

        $snippet = Snippet::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? 'Generated from MinIO IAM Generator',
            'command' => $validated['command'],
            'language' => 'bash',
            'platform' => 'minio',
            'created_by' => $request->user()->id,
        ]);

        $activity->log($request->user(), 'created', $snippet, 'From tool: '.$tool->title);

        return redirect()->route('snippets.edit', $snippet)->with('success', 'Snippet saved from tool.');
    }
}
