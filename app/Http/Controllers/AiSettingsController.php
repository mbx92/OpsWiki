<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\AiAssistantService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AiSettingsController extends Controller
{
    public function edit(AiAssistantService $ai): Response
    {
        return Inertia::render('Settings/Integrations/Ai', [
            'ai' => Setting::aiForForm(),
            'connected' => $ai->isEnabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'base_url' => 'nullable|url|max:255',
            'api_key' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:100',
            'system_prompt' => 'nullable|string|max:2000',
        ]);

        Setting::saveAi($validated);

        return back()->with('success', 'AI assistant settings saved.');
    }
}
