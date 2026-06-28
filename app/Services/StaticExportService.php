<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Page;
use App\Services\MarkdownService;
use ZipArchive;

class StaticExportService
{
    public function __construct(
        private MarkdownService $markdown,
    ) {}

    public function exportBook(Book $book): string
    {
        $book->load(['pages' => fn ($q) => $q->orderBy('sort_order')]);

        return $this->buildZip(
            $book->slug,
            $book->title,
            $book->pages,
            $book->pages->pluck('title', 'slug')->all(),
        );
    }

    public function exportWiki(): string
    {
        $pages = Page::query()->orderBy('title')->get();

        $nav = $pages->pluck('title', 'slug')->all();

        return $this->buildZip('wiki', 'OpsWiki Documentation', $pages, $nav);
    }

    /**
     * @param  iterable<Page>  $pages
     * @param  array<string, string>  $nav
     */
    private function buildZip(string $basename, string $siteTitle, iterable $pages, array $nav): string
    {
        $zipPath = tempnam(sys_get_temp_dir(), 'static_export_');
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $css = $this->stylesheet();
        $zip->addFromString('assets/style.css', $css);

        $index = $this->renderIndex($siteTitle, $nav);
        $zip->addFromString('index.html', $index);

        foreach ($pages as $page) {
            $html = $this->renderPage($siteTitle, $nav, $page);
            $zip->addFromString('pages/'.$page->slug.'.html', $html);
        }

        $zip->close();
        $content = file_get_contents($zipPath);
        unlink($zipPath);

        return $content;
    }

    /**
     * @param  array<string, string>  $nav
     */
    private function renderIndex(string $siteTitle, array $nav): string
    {
        $items = '';
        foreach ($nav as $slug => $title) {
            $items .= '<li><a href="pages/'.htmlspecialchars($slug).'.html">'.htmlspecialchars($title).'</a></li>';
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$this->e($siteTitle)}</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar"><h1>{$this->e($siteTitle)}</h1><nav><ul>{$items}</ul></nav></aside>
<main class="content"><h1>{$this->e($siteTitle)}</h1><p>Select a page from the sidebar.</p></main>
</div>
</body>
</html>
HTML;
    }

    /**
     * @param  array<string, string>  $nav
     */
    private function renderPage(string $siteTitle, array $nav, Page $page): string
    {
        $items = '';
        foreach ($nav as $slug => $title) {
            $active = $slug === $page->slug ? ' class="active"' : '';
            $items .= '<li><a href="'.htmlspecialchars($slug).'.html"'.$active.'>'.htmlspecialchars($title).'</a></li>';
        }

        $body = filled($page->content_html)
            ? $page->content_html
            : $this->markdown->toHtml($page->content_markdown ?? '');

        $summary = filled($page->summary)
            ? '<p class="summary">'.$this->e($page->summary).'</p>'
            : '';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$this->e($page->title)} — {$this->e($siteTitle)}</title>
<link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<div class="layout">
<aside class="sidebar"><h1><a href="../index.html">{$this->e($siteTitle)}</a></h1><nav><ul>{$items}</ul></nav></aside>
<main class="content">
<h1>{$this->e($page->title)}</h1>
{$summary}
<article class="prose">{$body}</article>
</main>
</div>
</body>
</html>
HTML;
    }

    private function stylesheet(): string
    {
        return <<<'CSS'
*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; font-family: system-ui, -apple-system, sans-serif; color: #111; background: #f8f9fa; line-height: 1.6; }
.layout { display: flex; min-height: 100vh; }
.sidebar { width: 260px; background: #fff; border-right: 1px solid #e5e7eb; padding: 1.5rem; position: sticky; top: 0; height: 100vh; overflow-y: auto; }
.sidebar h1 { font-size: 1rem; margin: 0 0 1rem; }
.sidebar h1 a { color: inherit; text-decoration: none; }
.sidebar ul { list-style: none; padding: 0; margin: 0; }
.sidebar li { margin: 0.25rem 0; }
.sidebar a { display: block; padding: 0.35rem 0.5rem; border-radius: 6px; color: #374151; text-decoration: none; font-size: 0.875rem; }
.sidebar a:hover, .sidebar a.active { background: #f0f0f0; color: #111; font-weight: 600; }
.content { flex: 1; padding: 2rem; max-width: 800px; }
.summary { color: #6b7280; font-size: 1.05rem; }
.prose pre { background: #101010; color: #e5e7eb; padding: 1rem; border-radius: 8px; overflow-x: auto; }
.prose code { background: #f3f4f6; padding: 0.1rem 0.35rem; border-radius: 4px; font-size: 0.875em; }
.prose pre code { background: none; padding: 0; }
.prose h2, .prose h3 { margin-top: 1.5rem; }
CSS;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
