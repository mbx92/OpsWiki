<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class HtmlParserService
{
    private const REMOVE_TAGS = [
        'script', 'style', 'nav', 'header', 'footer', 'aside', 'noscript',
        'iframe', 'form', 'button', 'input', 'select', 'textarea', 'meta',
        'link', 'svg', 'canvas', 'object', 'embed',
    ];

    private const STRIP_ATTRIBUTES = [
        'style', 'class', 'id', 'onclick', 'onload', 'onerror', 'width', 'height',
        'align', 'valign', 'bgcolor', 'color', 'face', 'border', 'cellpadding', 'cellspacing',
    ];

    /**
     * Parse raw HTML into clean, design-ready article markup.
     */
    public function parse(string $html, ?string $pageTitle = null): string
    {
        if (trim($html) === '') {
            return '';
        }

        $dom = $this->loadDom($html);
        $root = $this->findContentRoot($dom);

        if (! $root) {
            return '';
        }

        $this->removeUnwantedNodes($root);
        $this->removeEmbeddedTableOfContents($root);
        $this->stripAttributes($root);
        $this->unwrapRedundantContainers($root);
        $this->normalizeHeadings($root, $pageTitle);
        $this->wrapTables($root);
        $this->normalizeCodeBlocks($root);
        $this->normalizeLinks($root);
        $this->normalizeImages($root);
        $this->removeEmptyNodes($root);

        $inner = $this->innerHtml($root);

        if (trim(strip_tags($inner)) === '') {
            return '';
        }

        return '<article class="wiki-content">'.$inner.'</article>';
    }

    private function loadDom(string $html): DOMDocument
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);

        $wrapped = str_contains(strtolower($html), '<html')
            ? $html
            : '<html><body><div id="wiki-root">'.$html.'</div></body></html>';

        $dom->loadHTML(
            '<?xml encoding="UTF-8">'.$wrapped,
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET,
        );

        libxml_clear_errors();

        return $dom;
    }

    private function findContentRoot(DOMDocument $dom): ?DOMElement
    {
        $xpath = new DOMXPath($dom);

        $queries = [
            '//main[1]',
            '//article[1]',
            '//*[@role="main"][1]',
            '//div[contains(concat(" ", normalize-space(@class), " "), " content ")][1]',
            '//div[contains(concat(" ", normalize-space(@class), " "), " markdown-body ")][1]',
            '//div[contains(concat(" ", normalize-space(@class), " "), " post-content ")][1]',
            '//div[contains(concat(" ", normalize-space(@class), " "), " documentation ")][1]',
            '//div[@id="content"][1]',
            '//div[@id="main-content"][1]',
            '//div[@id="wiki-root"][1]',
            '//body[1]',
        ];

        foreach ($queries as $query) {
            $node = $xpath->query($query)->item(0);

            if ($node instanceof DOMElement) {
                return $node;
            }
        }

        return $dom->documentElement instanceof DOMElement ? $dom->documentElement : null;
    }

    private function removeUnwantedNodes(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);

        foreach (self::REMOVE_TAGS as $tag) {
            $nodes = $xpath->query('.//'.$tag, $root);

            if (! $nodes) {
                continue;
            }

            /** @var DOMNode $node */
            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        // Remove HTML comments
        $comments = $xpath->query('.//comment()', $root);

        if ($comments) {
            foreach (iterator_to_array($comments) as $comment) {
                $comment->parentNode?->removeChild($comment);
            }
        }
    }

    private function stripAttributes(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);
        $nodes = $xpath->query('.//*', $root);

        if (! $nodes) {
            return;
        }

        foreach (iterator_to_array($nodes) as $node) {
            if (! $node instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($node->tagName);

            if ($tag === 'a') {
                $href = $node->getAttribute('href');
                while ($node->attributes->length) {
                    $node->removeAttribute($node->attributes->item(0)->nodeName);
                }
                if ($href !== '') {
                    $node->setAttribute('href', $href);
                    $node->setAttribute('rel', 'noopener noreferrer');
                }

                continue;
            }

            if ($tag === 'img') {
                $src = $node->getAttribute('src');
                $alt = $node->getAttribute('alt');
                while ($node->attributes->length) {
                    $node->removeAttribute($node->attributes->item(0)->nodeName);
                }
                if ($src !== '') {
                    $node->setAttribute('src', $src);
                }
                if ($alt !== '') {
                    $node->setAttribute('alt', $alt);
                }
                $node->setAttribute('loading', 'lazy');

                continue;
            }

            if ($tag === 'code' && $node->parentNode instanceof DOMElement && strtolower($node->parentNode->tagName) === 'pre') {
                continue;
            }

            foreach (self::STRIP_ATTRIBUTES as $attr) {
                $node->removeAttribute($attr);
            }

            // Strip namespaced/data attributes
            if ($node->hasAttributes()) {
                $toRemove = [];
                foreach ($node->attributes as $attr) {
                    if (str_starts_with($attr->name, 'data-') || str_contains($attr->name, ':')) {
                        $toRemove[] = $attr->name;
                    }
                }
                foreach ($toRemove as $name) {
                    $node->removeAttribute($name);
                }
            }
        }
    }

    private function unwrapRedundantContainers(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);

        for ($i = 0; $i < 3; $i++) {
            $divs = $xpath->query('.//div[not(@class) and not(@id)]', $root);

            if (! $divs || $divs->length === 0) {
                break;
            }

            /** @var DOMElement $div */
            foreach (iterator_to_array($divs) as $div) {
                if ($div->childNodes->length === 1 && $div->parentNode) {
                    $child = $div->firstChild;
                    $div->parentNode->insertBefore($child, $div);
                    $div->parentNode->removeChild($div);
                }
            }
        }
    }

    private function removeEmbeddedTableOfContents(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);

        $tocQueries = [
            './/*[contains(concat(" ", normalize-space(@class), " "), " toc ")]',
            './/*[contains(concat(" ", normalize-space(@class), " "), " table-of-contents ")]',
            './/*[contains(concat(" ", normalize-space(@class), " "), " table_of_contents ")]',
            './/*[contains(concat(" ", normalize-space(@class), " "), " daftar-isi ")]',
            './/*[@id="toc" or @id="table-of-contents" or @id="daftar-isi"]',
            './/*[@role="doc-toc"]',
        ];

        foreach ($tocQueries as $query) {
            $nodes = $xpath->query($query, $root);

            if (! $nodes) {
                continue;
            }

            foreach (iterator_to_array($nodes) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        $this->removeTocOnlyContainers($root);

        $headings = $xpath->query('.//h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6', $root);

        if (! $headings) {
            return;
        }

        /** @var DOMElement $heading */
        foreach (iterator_to_array($headings) as $heading) {
            $text = trim($heading->textContent ?? '');

            if (! $this->isTocHeading($text)) {
                continue;
            }

            $toRemove = [$heading];
            $sibling = $heading->nextSibling;

            while ($sibling) {
                if (! $sibling instanceof DOMElement) {
                    $toRemove[] = $sibling;
                    $sibling = $sibling->nextSibling;

                    continue;
                }

                $tag = strtolower($sibling->tagName);

                if (in_array($tag, ['ul', 'ol', 'nav'], true)) {
                    $toRemove[] = $sibling;
                    $sibling = $sibling->nextSibling;

                    continue;
                }

                if ($tag === 'a' && str_starts_with($sibling->getAttribute('href'), '#')) {
                    $toRemove[] = $sibling;
                    $sibling = $sibling->nextSibling;

                    continue;
                }

                if (in_array($tag, ['div', 'section', 'p'], true) && $this->looksLikeTocList($sibling)) {
                    $toRemove[] = $sibling;
                    $sibling = $sibling->nextSibling;

                    continue;
                }

                break;
            }

            foreach ($toRemove as $node) {
                $node->parentNode?->removeChild($node);
            }
        }
    }

    /**
     * Clean already-stored article HTML (e.g. remove embedded TOC from imported pages).
     */
    public function cleanStoredHtml(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>',
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET,
        );
        libxml_clear_errors();

        $xpath = new DOMXPath($dom);
        $root = $xpath->query('//article[contains(@class, "wiki-content")]')->item(0)
            ?? $xpath->query('//body/*[1]')->item(0);

        if (! $root instanceof DOMElement) {
            return $html;
        }

        $this->removeEmbeddedTableOfContents($root);
        $this->removeEmptyNodes($root);

        $inner = $this->innerHtml($root);

        if (strtolower($root->tagName) === 'article') {
            return '<article class="wiki-content">'.$inner.'</article>';
        }

        return $inner;
    }

    private function removeTocOnlyContainers(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);
        $containers = $xpath->query('.//section | .//div', $root);

        if (! $containers) {
            return;
        }

        $toRemove = [];

        foreach (iterator_to_array($containers) as $container) {
            if ($container instanceof DOMElement && $this->isTocOnlyContainer($container)) {
                $toRemove[] = $container;
            }
        }

        foreach ($toRemove as $node) {
            $node->parentNode?->removeChild($node);
        }
    }

    private function isTocOnlyContainer(DOMElement $container): bool
    {
        $hasTocHeading = false;
        $anchorCount = 0;
        $otherContent = false;

        foreach ($container->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                if (trim($child->textContent ?? '') === '') {
                    continue;
                }
                $otherContent = true;

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'], true)) {
                if ($this->isTocHeading(trim($child->textContent ?? ''))) {
                    $hasTocHeading = true;
                } else {
                    $otherContent = true;
                }

                continue;
            }

            if ($tag === 'a' && str_starts_with($child->getAttribute('href'), '#')) {
                $anchorCount++;

                continue;
            }

            if (in_array($tag, ['ul', 'ol', 'nav'], true) && $this->looksLikeTocList($child)) {
                $anchorCount += 2;

                continue;
            }

            $otherContent = true;
        }

        return $hasTocHeading && $anchorCount >= 2 && ! $otherContent;
    }

    private function isTocHeading(string $text): bool
    {
        return (bool) preg_match('/^(daftar\s+isi|table\s+of\s+contents|contents|on\s+this\s+page|in\s+this\s+article)$/iu', $text);
    }

    private function looksLikeTocList(DOMElement $node): bool
    {
        $xpath = new DOMXPath($node->ownerDocument);
        $anchorLinks = $xpath->query('.//a[starts-with(@href, "#")]', $node);
        $listItems = $xpath->query('.//li', $node);

        if ($anchorLinks && $anchorLinks->length >= 2) {
            return true;
        }

        return $listItems && $listItems->length >= 2 && $anchorLinks && $anchorLinks->length >= 1;
    }

    private function normalizeHeadings(DOMElement $root, ?string $pageTitle): void
    {
        if (! $pageTitle) {
            return;
        }

        $xpath = new DOMXPath($root->ownerDocument);
        $h1 = $xpath->query('.//h1[1]', $root)?->item(0);

        if ($h1 instanceof DOMElement) {
            $text = trim($h1->textContent ?? '');

            if (strcasecmp($text, $pageTitle) === 0) {
                $h1->parentNode?->removeChild($h1);
            }
        }
    }

    private function wrapTables(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);
        $tables = $xpath->query('.//table', $root);

        if (! $tables) {
            return;
        }

        /** @var DOMElement $table */
        foreach (iterator_to_array($tables) as $table) {
            if (! $table->parentNode) {
                continue;
            }

            $wrapper = $root->ownerDocument->createElement('div');
            $wrapper->setAttribute('class', 'wiki-table-wrap');
            $table->parentNode->insertBefore($wrapper, $table);
            $wrapper->appendChild($table);
        }
    }

    private function normalizeCodeBlocks(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);
        $pres = $xpath->query('.//pre', $root);

        if (! $pres) {
            return;
        }

        /** @var DOMElement $pre */
        foreach (iterator_to_array($pres) as $pre) {
            $pre->setAttribute('class', 'wiki-code-block');

            if ($pre->childNodes->length === 1 && $pre->firstChild instanceof DOMElement && strtolower($pre->firstChild->tagName) === 'code') {
                $pre->firstChild->setAttribute('class', 'wiki-code');
            }
        }

        // Inline code not inside pre
        $codes = $xpath->query('.//code[not(parent::pre)]', $root);

        if ($codes) {
            foreach (iterator_to_array($codes) as $code) {
                if ($code instanceof DOMElement) {
                    $code->setAttribute('class', 'wiki-inline-code');
                }
            }
        }
    }

    private function normalizeLinks(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);
        $links = $xpath->query('.//a[@href]', $root);

        if (! $links) {
            return;
        }

        foreach (iterator_to_array($links) as $link) {
            if (! $link instanceof DOMElement) {
                continue;
            }

            $href = $link->getAttribute('href');

            if (str_starts_with($href, 'javascript:')) {
                $link->parentNode?->replaceChild(
                    $root->ownerDocument->createTextNode($link->textContent ?? ''),
                    $link,
                );
            }
        }
    }

    private function normalizeImages(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);
        $images = $xpath->query('.//img', $root);

        if (! $images) {
            return;
        }

        foreach (iterator_to_array($images) as $img) {
            if ($img instanceof DOMElement) {
                $img->setAttribute('class', 'wiki-image');
            }
        }
    }

    private function removeEmptyNodes(DOMElement $root): void
    {
        $xpath = new DOMXPath($root->ownerDocument);

        for ($i = 0; $i < 5; $i++) {
            $nodes = $xpath->query('.//p | .//div | .//span', $root);

            if (! $nodes) {
                break;
            }

            $removed = false;

            foreach (iterator_to_array($nodes) as $node) {
                if (! $node instanceof DOMElement) {
                    continue;
                }

                $text = trim($node->textContent ?? '');
                $hasMedia = $node->getElementsByTagName('img')->length > 0
                    || $node->getElementsByTagName('table')->length > 0;

                if ($text === '' && ! $hasMedia && $node->parentNode) {
                    $node->parentNode->removeChild($node);
                    $removed = true;
                }
            }

            if (! $removed) {
                break;
            }
        }
    }

    private function innerHtml(DOMElement $node): string
    {
        $html = '';

        foreach ($node->childNodes as $child) {
            $html .= $node->ownerDocument->saveHTML($child);
        }

        return trim($html);
    }
}
