<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\MinioArchiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MinioSettingsController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Settings/Integrations/Minio', [
            'minio' => Setting::minioForForm(),
            'recentArchives' => \App\Models\ArchiveRecord::query()
                ->latest()
                ->limit(10)
                ->get(['id', 'type', 'original_name', 'stored_path', 'bucket', 'size', 'created_at']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'enabled' => 'boolean',
            'endpoint' => 'nullable|string|max:255',
            'access_key' => 'nullable|string|max:255',
            'secret_key' => 'nullable|string|max:255',
            'bucket' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:50',
            'use_path_style_endpoint' => 'boolean',
            'archive_prefix' => 'nullable|string|max:100',
            'archive_imports' => 'boolean',
            'archive_exports' => 'boolean',
            'archive_uploads' => 'boolean',
        ]);

        Setting::saveMinio($validated);

        return back()->with('success', 'MinIO settings saved.');
    }

    public function test(MinioArchiveService $archive): RedirectResponse
    {
        $result = $archive->testConnection();

        return back()->with(
            $result['ok'] ? 'success' : 'error',
            $result['message'],
        );
    }
}
