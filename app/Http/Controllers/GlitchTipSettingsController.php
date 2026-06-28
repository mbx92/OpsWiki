<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\GlitchTipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GlitchTipSettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Settings/Integrations/Glitchtip', [
            'glitchtip' => Setting::glitchtipForForm(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'dsn' => 'nullable|string|max:500',
            'environment' => 'nullable|string|max:50',
            'traces_sample_rate' => 'nullable|numeric|min:0|max:1',
            'send_default_pii' => 'boolean',
        ]);

        Setting::saveGlitchtip($validated);

        return back()->with('success', 'GlitchTip settings saved.');
    }

    public function test(GlitchTipService $glitchtip): RedirectResponse
    {
        $result = $glitchtip->testConnection();

        return back()->with(
            $result['ok'] ? 'success' : 'error',
            $result['message'],
        );
    }
}
