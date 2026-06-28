<?php

namespace App\Services;

use App\Models\Page;
use App\Models\PageVersion;
use App\Models\User;

class PageVersionService
{
    public function snapshot(Page $page, ?User $user = null): ?PageVersion
    {
        if (! $this->hasContent($page)) {
            return null;
        }

        $versionNumber = (int) $page->versions()->max('version_number') + 1;

        return PageVersion::create([
            'page_id' => $page->id,
            'version_number' => $versionNumber,
            'title' => $page->title,
            'slug' => $page->slug,
            'summary' => $page->summary,
            'content_markdown' => $page->content_markdown,
            'content_html' => $page->content_html,
            'status' => $page->status,
            'visibility' => $page->visibility,
            'created_by' => $user?->id ?? $page->updated_by,
        ]);
    }

    public function restore(Page $page, PageVersion $version, User $user, MarkdownService $markdown): Page
    {
        $this->snapshot($page, $user);

        $page->update([
            'title' => $version->title,
            'summary' => $version->summary,
            'content_markdown' => $version->content_markdown,
            'content_html' => $version->content_html ?? $markdown->toHtml($version->content_markdown),
            'status' => $version->status,
            'visibility' => $version->visibility,
            'updated_by' => $user->id,
        ]);

        return $page->fresh();
    }

    private function hasContent(Page $page): bool
    {
        return filled($page->content_markdown) || filled($page->content_html) || filled($page->title);
    }
}
