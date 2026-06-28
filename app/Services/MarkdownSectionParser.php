<?php

namespace App\Services;

use Illuminate\Support\Str;

class MarkdownSectionParser
{
    /**
     * @param  array<string, list<string>>  $sectionAliases
     * @return array{title: string, sections: array<string, string>}
     */
    public function parse(string $content, array $sectionAliases, ?string $titlePrefix = null): array
    {
        $content = str_replace("\r\n", "\n", trim($content));
        $title = $this->extractTitle($content, $titlePrefix);
        $rawSections = $this->splitSections($content);
        $sections = [];

        foreach ($rawSections as $heading => $body) {
            $field = $this->resolveField($heading, $sectionAliases);

            if ($field === null) {
                continue;
            }

            $body = trim($body);

            if ($body === '') {
                continue;
            }

            $sections[$field] = isset($sections[$field])
                ? $sections[$field]."\n\n".$body
                : $body;
        }

        return compact('title', 'sections');
    }

    private function extractTitle(string $content, ?string $titlePrefix): string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            $title = trim($matches[1]);

            if ($titlePrefix) {
                $title = preg_replace('/^'.preg_quote($titlePrefix, '/').':\s*/i', '', $title) ?? $title;
            }

            return trim($title);
        }

        return '';
    }

    /**
     * @return array<string, string>
     */
    private function splitSections(string $content): array
    {
        $sections = [];
        $parts = preg_split('/^##\s+(.+)$/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE) ?: [];

        for ($i = 1; $i < count($parts); $i += 2) {
            $heading = trim($parts[$i]);
            $body = trim($parts[$i + 1] ?? '');

            if ($heading !== '') {
                $sections[$heading] = $body;
            }
        }

        return $sections;
    }

    /**
     * @param  array<string, list<string>>  $sectionAliases
     */
    private function resolveField(string $heading, array $sectionAliases): ?string
    {
        $normalized = Str::lower(trim($heading));

        foreach ($sectionAliases as $field => $aliases) {
            foreach ($aliases as $alias) {
                if ($normalized === Str::lower($alias)) {
                    return $field;
                }
            }
        }

        return null;
    }

    public function humanizeFilename(string $basename): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $basename));
    }
}
