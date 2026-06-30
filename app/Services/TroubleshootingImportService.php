<?php

namespace App\Services;

use App\Models\TroubleshootingCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class TroubleshootingImportService
{
    /** @var array<string, list<string>> */
    private const SECTION_ALIASES = [
        'symptoms' => ['gejala yang terlihat', 'gejala', 'symptoms'],
        'environment' => ['ringkasan kasus', 'environment', 'lingkungan', 'konteks'],
        'error_log' => ['connection tracking', 'hasil test', 'error log', 'error asli', 'error', 'log error'],
        'suspected_causes' => [
            'root cause',
            'akar masalah',
            'kenapa masalah ini terjadi',
            'dugaan penyebab',
            'suspected causes',
            'penyebab',
        ],
        'diagnosis_steps' => ['langkah diagnosis', 'diagnosis steps', 'langkah-langkah diagnosis', 'diagnosis', 'pengecekan'],
        'working_solution' => [
            'solusi yang disarankan',
            'solusi cepat',
            'solusi yang berhasil',
            'kesimpulan akhir',
            'working solution',
            'solusi',
        ],
        'failed_attempts' => ['vlan/subnet yang gagal', 'subnet yang gagal', 'failed attempts', 'solusi yang tidak berhasil', 'percobaan gagal'],
        'validation' => ['checklist verifikasi', 'verifikasi', 'validation', 'validasi'],
        'prevention' => ['solusi jangka panjang', 'catatan penting', 'pencegahan', 'prevention'],
    ];

    public function __construct(
        private ImportFileReader $files,
        private MarkdownSectionParser $parser,
    ) {}

    /**
     * @param  list<UploadedFile>  $uploadedFiles
     * @return array{cases: TroubleshootingCase[], imported: int, skipped: int}
     */
    public function import(array $uploadedFiles, int $userId, string $status = 'open', string $severity = 'medium'): array
    {
        $cases = [];
        $imported = 0;
        $skipped = 0;

        foreach ($this->files->entriesFromUploads($uploadedFiles) as $entry) {
            $parsed = $this->parser->parse($entry['content'], self::SECTION_ALIASES, 'Troubleshooting', 'working_solution');
            $basename = pathinfo($entry['name'], PATHINFO_FILENAME);
            $title = $parsed['title'] !== '' ? $parsed['title'] : $this->parser->humanizeFilename($basename);

            if ($title === '') {
                $skipped++;

                continue;
            }

            $cases[] = TroubleshootingCase::create([
                'title' => $title,
                'slug' => TroubleshootingCase::uniqueSlug(Str::slug($basename ?: $title)),
                'symptoms' => $parsed['sections']['symptoms'] ?? null,
                'environment' => $parsed['sections']['environment'] ?? null,
                'error_log' => $parsed['sections']['error_log'] ?? null,
                'suspected_causes' => $parsed['sections']['suspected_causes'] ?? null,
                'diagnosis_steps' => $parsed['sections']['diagnosis_steps'] ?? null,
                'working_solution' => $parsed['sections']['working_solution'] ?? null,
                'failed_attempts' => $parsed['sections']['failed_attempts'] ?? null,
                'validation' => $parsed['sections']['validation'] ?? null,
                'prevention' => $parsed['sections']['prevention'] ?? null,
                'severity' => $severity,
                'status' => $status,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
            $imported++;
        }

        return compact('cases', 'imported', 'skipped');
    }

    /**
     * @return array{title: string, sections: array<string, string>}
     */
    public function parseContent(string $content, string $fallbackTitle): array
    {
        $parsed = $this->parser->parse($content, self::SECTION_ALIASES, 'Troubleshooting', 'working_solution');

        if ($parsed['sections'] === []) {
            $body = preg_replace('/^#\s+.+$/m', '', $content, 1) ?? $content;
            $parsed['sections']['working_solution'] = trim($body);
        }

        return [
            'title' => $parsed['title'] !== '' ? $parsed['title'] : $fallbackTitle,
            'sections' => $parsed['sections'],
        ];
    }
}
