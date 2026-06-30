<?php

namespace App\Http\Controllers;

use App\Services\ContentImportParseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ContentImportParseController extends Controller
{
    /** @var array<string, string> */
    private const PERMISSIONS = [
        'wiki' => 'wiki.create',
        'sop' => 'sops.manage',
        'troubleshooting' => 'troubleshooting.manage',
        'snippet' => 'snippets.manage',
    ];

    public function __invoke(Request $request, string $type, ContentImportParseService $parser): JsonResponse
    {
        $permission = self::PERMISSIONS[$type] ?? null;

        abort_if($permission === null, 404);
        abort_unless($request->user()?->hasPermission($permission), 403);

        $validated = $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $file = $validated['file'];
        $extension = strtolower($file->getClientOriginalExtension());

        $allowed = match ($type) {
            'wiki' => ['md', 'markdown', 'html', 'htm'],
            'snippet' => ['md', 'markdown', 'html', 'htm', 'txt'],
            default => ['md', 'markdown'],
        };

        if (! in_array($extension, $allowed, true)) {
            return response()->json([
                'message' => 'Unsupported file type for this content.',
            ], 422);
        }

        try {
            return response()->json($parser->parse($type, $file));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
