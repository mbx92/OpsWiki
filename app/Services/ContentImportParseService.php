<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ContentImportParseService
{
    public function __construct(
        private PageImportService $pages,
        private SopImportService $sops,
        private TroubleshootingImportService $troubleshooting,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function parse(string $type, UploadedFile $file): array
    {
        return match ($type) {
            'wiki' => $this->pages->parseUploadForPrefill($file),
            'sop' => $this->flattenSections($this->sops->parseContent(
                $this->readText($file),
                $this->fallbackTitle($file),
            )),
            'troubleshooting' => $this->flattenSections($this->troubleshooting->parseContent(
                $this->readText($file),
                $this->fallbackTitle($file),
            )),
            'snippet' => $this->parseSnippet($file),
            default => throw new InvalidArgumentException("Unknown import type: {$type}"),
        };
    }

    /**
     * @return array<string, string|null>
     */
    private function parseSnippet(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $content = $this->readText($file);
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fallbackTitle = $this->fallbackTitle($file);

        if (in_array($extension, ['html', 'htm'], true)) {
            $title = $this->extractHtmlTitle($content) ?? $fallbackTitle;
            $command = $this->extractHtmlCode($content) ?? trim(strip_tags($content));

            return [
                'title' => $title,
                'description' => null,
                'command' => $command,
            ];
        }

        $title = $this->extractMarkdownTitle($content) ?? $fallbackTitle;

        if (preg_match('/```[\w.-]*\n(.*?)```/s', $content, $matches)) {
            $command = trim($matches[1]);
            $description = trim(preg_replace('/```[\w.-]*\n.*?```/s', '', $content) ?? '');
            $description = trim(preg_replace('/^#\s+.+$/m', '', $description) ?? '');

            return [
                'title' => $title,
                'description' => $description !== '' ? Str::limit($description, 500) : null,
                'command' => $command,
            ];
        }

        return [
            'title' => $title,
            'description' => null,
            'command' => trim($content),
        ];
    }

    private function readText(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['md', 'markdown', 'html', 'htm', 'txt'], true)) {
            throw new InvalidArgumentException('Unsupported file type.');
        }

        return str_replace("\r\n", "\n", trim($file->get()));
    }

    private function fallbackTitle(UploadedFile $file): string
    {
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        return Str::title(str_replace(['-', '_'], ' ', $basename));
    }

    /**
     * @param  array{title: string, sections: array<string, string>}  $parsed
     * @return array<string, string>
     */
    private function flattenSections(array $parsed): array
    {
        return array_merge(
            ['title' => $parsed['title']],
            $parsed['sections'],
        );
    }

    private function extractMarkdownTitle(string $content): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private function extractHtmlTitle(string $content): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $content, $matches)) {
            $title = trim(strip_tags($matches[1]));

            if ($title !== '') {
                return html_entity_decode($title);
            }
        }

        if (preg_match('/<h1[^>]*>(.*?)<\/h1>/is', $content, $matches)) {
            $title = trim(strip_tags($matches[1]));

            if ($title !== '') {
                return html_entity_decode($title);
            }
        }

        return null;
    }

    private function extractHtmlCode(string $content): ?string
    {
        if (preg_match('/<pre[^>]*><code[^>]*>(.*?)<\/code><\/pre>/is', $content, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1])));
        }

        if (preg_match('/<pre[^>]*>(.*?)<\/pre>/is', $content, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1])));
        }

        if (preg_match('/<code[^>]*>(.*?)<\/code>/is', $content, $matches)) {
            return trim(html_entity_decode(strip_tags($matches[1])));
        }

        return null;
    }
}
