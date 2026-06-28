<?php

namespace App\Services;

use Illuminate\Support\Str;

class MarkdownService
{
    public function toHtml(?string $markdown): string
    {
        if (empty($markdown)) {
            return '';
        }

        return Str::markdown($markdown, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }
}
