<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Page;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use ZipArchive;

class PageImportService
{
    public function __construct(
        private MarkdownService $markdown,
        private HtmlParserService $htmlParser,
    ) {}

    /**
     * @return array{book: Book, pages: Page[], imported: int, skipped: int}
     */
    public function import(
        string $bookTitle,
        array $files,
        int $userId,
        ?int $categoryId = null,
        ?string $bookDescription = null,
        string $status = 'draft',
        ?Book $book = null,
    ): array {
        if (! $book) {
            $book = Book::create([
                'title' => $bookTitle,
                'slug' => Book::uniqueSlug($bookTitle),
                'description' => $bookDescription,
                'status' => $status,
                'category_id' => $categoryId,
                'created_by' => $userId,
            ]);
            $sortOrder = 0;
        } else {
            $sortOrder = ((int) $book->pages()->max('sort_order')) + 1;

            if ($bookDescription !== null && $bookDescription !== '') {
                $book->update(['description' => $bookDescription]);
            }

            if ($categoryId !== null) {
                $book->update(['category_id' => $categoryId]);
            }
        }

        $pages = [];
        $imported = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $entries = $this->extractEntries($file);

            usort($entries, fn ($a, $b) => strcmp($a['name'], $b['name']));

            foreach ($entries as $entry) {
                if (! $this->isSupported($entry['name'])) {
                    $skipped++;

                    continue;
                }

                $page = $this->createPageFromEntry($book, $entry, $userId, $sortOrder++, $status);
                $pages[] = $page;
                $imported++;
            }
        }

        return compact('book', 'pages', 'imported', 'skipped');
    }

    /**
     * @return array<int, array{name: string, content: string, extension: string}>
     */
    private function extractEntries(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if ($extension === 'zip') {
            return $this->extractFromZip($file);
        }

        return [[
            'name' => $file->getClientOriginalName(),
            'content' => $file->get(),
            'extension' => $extension,
        ]];
    }

    /**
     * @return array<int, array{name: string, content: string, extension: string}>
     */
    private function extractFromZip(UploadedFile $file): array
    {
        $entries = [];
        $zip = new ZipArchive;

        if ($zip->open($file->getRealPath()) !== true) {
            return $entries;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            $name = $stat['name'];

            if (str_ends_with($name, '/') || str_starts_with(basename($name), '.')) {
                continue;
            }

            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

            if (! in_array($extension, ['md', 'markdown', 'html', 'htm'])) {
                continue;
            }

            $content = $zip->getFromIndex($i);

            if ($content === false) {
                continue;
            }

            $entries[] = [
                'name' => $name,
                'content' => $content,
                'extension' => $extension,
            ];
        }

        $zip->close();

        usort($entries, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $entries;
    }

    private function isSupported(string $filename): bool
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return in_array($extension, ['md', 'markdown', 'html', 'htm']);
    }

    /**
     * @return array{title: string, summary: ?string, content_markdown: ?string, content_html: ?string}
     */
    public function parseUploadForPrefill(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $content = $file->get();

        if (in_array($extension, ['md', 'markdown'], true)) {
            $title = $this->extractMarkdownTitle($content) ?? $this->humanizeFilename($basename);
            $markdown = $content;
            $html = $this->markdown->toHtml($markdown);

            return [
                'title' => $title,
                'summary' => $this->extractSummary($html, $markdown),
                'content_markdown' => $markdown,
                'content_html' => null,
            ];
        }

        if (in_array($extension, ['html', 'htm'], true)) {
            $title = $this->extractHtmlTitle($content) ?? $this->humanizeFilename($basename);
            $html = $this->htmlParser->parse($content, $title);

            return [
                'title' => $title,
                'summary' => $this->extractSummary($html, null),
                'content_markdown' => null,
                'content_html' => $html,
            ];
        }

        throw new \InvalidArgumentException('Unsupported file type.');
    }

    /**
     * @param  array{name: string, content: string, extension: string}  $entry
     */
    private function createPageFromEntry(Book $book, array $entry, int $userId, int $sortOrder, string $status): Page
    {
        $extension = $entry['extension'];
        $basename = pathinfo($entry['name'], PATHINFO_FILENAME);
        $content = $entry['content'];

        if (in_array($extension, ['md', 'markdown'])) {
            $title = $this->extractMarkdownTitle($content) ?? $this->humanizeFilename($basename);
            $markdown = $content;
            $html = $this->markdown->toHtml($markdown);
        } else {
            $title = $this->extractHtmlTitle($content) ?? $this->humanizeFilename($basename);
            $html = $this->htmlParser->parse($content, $title);
            $markdown = null;
        }

        $slugBase = Str::slug($basename ?: $title);
        $slug = Page::uniqueSlug($slugBase);

        return Page::create([
            'title' => $title,
            'slug' => $slug,
            'summary' => $this->extractSummary($html, $markdown),
            'content_markdown' => $markdown,
            'content_html' => $html,
            'book_id' => $book->id,
            'category_id' => $book->category_id,
            'sort_order' => $sortOrder,
            'status' => $status,
            'visibility' => 'private',
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    private function extractMarkdownTitle(string $content): ?string
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/^title:\s*["\']?(.+?)["\']?\s*$/mi', $content, $matches)) {
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

    private function extractSummary(?string $html, ?string $markdown): ?string
    {
        if ($markdown) {
            $blocks = preg_split('/\n\s*\n/', $markdown) ?: [];

            foreach ($blocks as $block) {
                $block = trim($block);

                if ($block === '' || str_starts_with($block, '#')) {
                    continue;
                }

                if (preg_match('/^[\*\-\+]\s/m', $block)) {
                    continue;
                }

                if (preg_match('/^daftar\s+isi$/iu', $block)) {
                    continue;
                }

                $text = trim(strip_tags($this->markdown->toHtml($block)));

                if ($text !== '' && ! $this->isTocLikeSummary($text)) {
                    return Str::limit($text, 200);
                }
            }

            return null;
        }

        if ($html) {
            if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $html, $matches)) {
                foreach ($matches[1] as $paragraph) {
                    $text = trim(html_entity_decode(strip_tags($paragraph)));

                    if ($text !== '' && ! $this->isTocLikeSummary($text)) {
                        return Str::limit($text, 200);
                    }
                }
            }

            $text = trim(strip_tags($html));

            if ($text !== '' && ! $this->isTocLikeSummary($text)) {
                return Str::limit($text, 200);
            }
        }

        return null;
    }

    private function isTocLikeSummary(string $text): bool
    {
        if (preg_match('/^daftar\s+isi\b/iu', $text)) {
            return true;
        }

        if (preg_match('/^(table\s+of\s+contents|contents|on\s+this\s+page)$/iu', trim($text))) {
            return true;
        }

        $withoutLabel = preg_replace('/^daftar\s+isi\s*/iu', '', $text) ?? $text;
        preg_match_all('/\d+\.\s+\S+/u', $withoutLabel, $sections);

        return count($sections[0] ?? []) >= 3;
    }

    private function humanizeFilename(string $basename): string
    {
        return Str::title(str_replace(['-', '_'], ' ', $basename));
    }
}
