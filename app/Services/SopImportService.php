<?php

namespace App\Services;

use App\Models\Sop;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class SopImportService
{
    /** @var array<string, list<string>> */
    private const SECTION_ALIASES = [
        'purpose' => ['tujuan', 'purpose'],
        'use_case' => ['use case', 'use-case', 'kasus penggunaan'],
        'requirements' => ['requirements', 'prasyarat', 'persyaratan'],
        'steps' => ['steps', 'langkah', 'langkah-langkah', 'prosedur'],
        'validation' => ['validation', 'validasi'],
        'rollback' => ['rollback', 'pemulihan'],
        'notes' => ['notes', 'catatan'],
    ];

    public function __construct(
        private ImportFileReader $files,
        private MarkdownSectionParser $parser,
    ) {}

    /**
     * @param  list<UploadedFile>  $uploadedFiles
     * @return array{sops: Sop[], imported: int, skipped: int}
     */
    public function import(array $uploadedFiles, int $userId, string $status = 'draft'): array
    {
        $sops = [];
        $imported = 0;
        $skipped = 0;

        foreach ($this->files->entriesFromUploads($uploadedFiles) as $entry) {
            $parsed = $this->parser->parse($entry['content'], self::SECTION_ALIASES, 'SOP');
            $basename = pathinfo($entry['name'], PATHINFO_FILENAME);
            $title = $parsed['title'] !== '' ? $parsed['title'] : $this->parser->humanizeFilename($basename);

            if ($title === '') {
                $skipped++;

                continue;
            }

            $sops[] = Sop::create([
                'title' => $title,
                'slug' => Sop::uniqueSlug(Str::slug($basename ?: $title)),
                'purpose' => $parsed['sections']['purpose'] ?? null,
                'use_case' => $parsed['sections']['use_case'] ?? null,
                'requirements' => $parsed['sections']['requirements'] ?? null,
                'steps' => $parsed['sections']['steps'] ?? null,
                'validation' => $parsed['sections']['validation'] ?? null,
                'rollback' => $parsed['sections']['rollback'] ?? null,
                'notes' => $parsed['sections']['notes'] ?? null,
                'status' => $status,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $imported++;
        }

        return compact('sops', 'imported', 'skipped');
    }

    /**
     * @return array{title: string, sections: array<string, string>}
     */
    public function parseContent(string $content, string $fallbackTitle): array
    {
        $parsed = $this->parser->parse($content, self::SECTION_ALIASES, 'SOP');

        return [
            'title' => $parsed['title'] !== '' ? $parsed['title'] : $fallbackTitle,
            'sections' => $parsed['sections'],
        ];
    }
}
