<?php

namespace App\Services;

use App\Models\ArchiveRecord;
use App\Models\Setting;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MinioArchiveService
{
    public const DISK = 'minio';

    public function config(): array
    {
        return Setting::getMinio();
    }

    public function isEnabled(): bool
    {
        $config = $this->config();

        return (bool) ($config['enabled'] ?? false)
            && $this->hasCredentials($config);
    }

    public function shouldArchiveImports(): bool
    {
        $config = $this->config();

        return $this->isEnabled() && (bool) ($config['archive_imports'] ?? true);
    }

    public function shouldArchiveExports(): bool
    {
        $config = $this->config();

        return $this->isEnabled() && (bool) ($config['archive_exports'] ?? true);
    }

    public function shouldArchiveUploads(): bool
    {
        $config = $this->config();

        return $this->isEnabled() && (bool) ($config['archive_uploads'] ?? false);
    }

    public function registerDisk(): void
    {
        $config = $this->config();

        if (! $this->hasCredentials($config)) {
            return;
        }

        Config::set('filesystems.disks.'.self::DISK, [
            'driver' => 's3',
            'key' => $config['access_key'],
            'secret' => $config['secret_key'],
            'region' => $config['region'] ?? 'us-east-1',
            'bucket' => $config['bucket'],
            'endpoint' => $config['endpoint'],
            'use_path_style_endpoint' => (bool) ($config['use_path_style_endpoint'] ?? true),
            'throw' => true,
        ]);
    }

    public function disk(): Filesystem
    {
        $this->registerDisk();

        return Storage::disk(self::DISK);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function testConnection(): array
    {
        $config = $this->config();

        if (! $this->hasCredentials($config)) {
            return ['ok' => false, 'message' => 'Endpoint, access key, secret key, and bucket are required.'];
        }

        try {
            $this->registerDisk();
            $disk = Storage::disk(self::DISK);
            $probe = rtrim($config['archive_prefix'] ?? 'archive', '/').'/.connection-test';

            $disk->put($probe, 'ok-'.now()->toIso8601String());
            $disk->delete($probe);

            return ['ok' => true, 'message' => 'Connected to bucket "'.$config['bucket'].'" successfully.'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function archiveUploadedFile(
        UploadedFile $file,
        string $type,
        ?Model $related = null,
        ?int $userId = null,
        array $metadata = [],
    ): ?ArchiveRecord {
        if (! $this->shouldArchiveType($type)) {
            return null;
        }

        try {
            $contents = file_get_contents($file->getRealPath());
            if ($contents === false) {
                return null;
            }

            return $this->store(
                contents: $contents,
                originalName: $file->getClientOriginalName(),
                mimeType: $file->getMimeType() ?? 'application/octet-stream',
                type: $type,
                related: $related,
                userId: $userId,
                metadata: $metadata,
            );
        } catch (\Throwable $e) {
            Log::warning('MinIO archive failed for upload', [
                'type' => $type,
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function archiveContent(
        string $contents,
        string $originalName,
        string $mimeType,
        string $type,
        ?Model $related = null,
        ?int $userId = null,
        array $metadata = [],
    ): ?ArchiveRecord {
        if (! $this->shouldArchiveType($type)) {
            return null;
        }

        try {
            return $this->store(
                contents: $contents,
                originalName: $originalName,
                mimeType: $mimeType,
                type: $type,
                related: $related,
                userId: $userId,
                metadata: $metadata,
            );
        } catch (\Throwable $e) {
            Log::warning('MinIO archive failed for content', [
                'type' => $type,
                'file' => $originalName,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function resolvePrimaryDisk(): string
    {
        if ($this->isEnabled()) {
            return self::DISK;
        }

        if (config('filesystems.disks.s3.key') && config('filesystems.disks.s3.bucket')) {
            return 's3';
        }

        return 'public';
    }

    private function shouldArchiveType(string $type): bool
    {
        return match ($type) {
            'import' => $this->shouldArchiveImports(),
            'export' => $this->shouldArchiveExports(),
            'upload' => $this->shouldArchiveUploads(),
            default => $this->isEnabled(),
        };
    }

    private function store(
        string $contents,
        string $originalName,
        string $mimeType,
        string $type,
        ?Model $related,
        ?int $userId,
        array $metadata,
    ): ArchiveRecord {
        $config = $this->config();
        $this->registerDisk();

        $safeName = Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) ?: 'file';
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = $safeName.($extension ? '.'.$extension : '');
        $path = $this->buildArchivePath($type, $filename);

        $this->disk()->put($path, $contents);

        return ArchiveRecord::create([
            'type' => $type,
            'original_name' => $originalName,
            'stored_path' => $path,
            'bucket' => $config['bucket'],
            'size' => strlen($contents),
            'mime_type' => $mimeType,
            'related_type' => $related ? $related->getMorphClass() : null,
            'related_id' => $related?->getKey(),
            'metadata' => $metadata ?: null,
            'archived_by' => $userId,
        ]);
    }

    private function buildArchivePath(string $type, string $filename): string
    {
        $config = $this->config();
        $prefix = trim($config['archive_prefix'] ?? 'archive', '/');
        $date = now()->format('Y/m/d');

        return $prefix.'/'.$type.'s/'.$date.'/'.Str::uuid().'_'.$filename;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function hasCredentials(array $config): bool
    {
        return ! empty($config['endpoint'])
            && ! empty($config['access_key'])
            && ! empty($config['secret_key'])
            && ! empty($config['bucket']);
    }
}
