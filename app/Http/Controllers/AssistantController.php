<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AiAssistantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AssistantController extends Controller
{
    public function index(AiAssistantService $ai): Response
    {
        return Inertia::render('Assistant/Index', [
            'enabled' => $ai->isEnabled(),
        ]);
    }

    public function chat(Request $request, AiAssistantService $ai): JsonResponse
    {
        $validated = $request->validate([
            'messages' => 'required|array|min:1',
            'messages.*.role' => 'required|in:user,assistant',
            'messages.*.content' => 'required|string|max:8000',
        ]);

        $result = $ai->chat($validated['messages']);

        return response()->json($result, $result['ok'] ? 200 : 422);
    }
}
