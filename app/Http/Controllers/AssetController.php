<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Page;
use App\Services\MinioArchiveService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AssetController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Asset::with('uploader')->latest();

        if ($search = $request->get('q')) {
            $query->where('original_name', 'ilike', "%{$search}%");
        }

        if ($mime = $request->get('mime')) {
            if ($mime === 'image') {
                $query->where('mime_type', 'ilike', 'image/%');
            } elseif ($mime === 'document') {
                $query->where(function ($q) {
                    $q->where('mime_type', 'ilike', 'application/pdf')
                        ->orWhere('mime_type', 'ilike', 'text/%');
                });
            }
        }

        if ($request->boolean('linked')) {
            $query->whereNotNull('related_id');
        }

        return Inertia::render('Assets/Index', [
            'assets' => $query->paginate(20)->withQueryString(),
            'filters' => $request->only(['q', 'mime', 'linked']),
        ]);
    }

    public function store(Request $request, MinioArchiveService $archive): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240|mimes:jpg,jpeg,png,gif,webp,svg,pdf,txt,md,zip,tar,gz,yml,yaml,json,conf',
            'page_id' => 'nullable|exists:pages,id',
        ]);

        $file = $request->file('file');
        $disk = $archive->resolvePrimaryDisk();
        if ($disk === MinioArchiveService::DISK) {
            $archive->registerDisk();
        }
        $path = 'assets/'.date('Y/m').'/'.Str::uuid().'_'.$file->getClientOriginalName();

        Storage::disk($disk)->put($path, file_get_contents($file->getRealPath()));

        $bucket = $disk === 'public'
            ? null
            : ($disk === MinioArchiveService::DISK
                ? $archive->config()['bucket']
                : config('filesystems.disks.s3.bucket'));

        $url = $disk === 'public'
            ? Storage::disk('public')->url($path)
            : Storage::disk($disk)->url($path);

        $relatedType = null;
        $relatedId = null;

        if ($request->page_id) {
            $relatedType = Page::class;
            $relatedId = $request->page_id;
        }

        Asset::create([
            'filename' => basename($path),
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $disk,
            'bucket' => $bucket,
            'path' => $path,
            'url' => $url,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'uploaded_by' => $request->user()->id,
        ]);

        $related = $relatedId ? Page::find($relatedId) : null;
        $archive->archiveUploadedFile(
            $file,
            'upload',
            $related,
            $request->user()->id,
            ['asset_path' => $path],
        );

        return back()->with('success', 'File uploaded.');
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        Storage::disk($asset->disk)->delete($asset->path);
        $asset->delete();

        return back()->with('success', 'Asset deleted.');
    }

}
