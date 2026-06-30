import { marked } from 'marked';
import DOMPurify from 'dompurify';

marked.setOptions({
    gfm: true,
    breaks: true,
});

const sanitize = (html) => DOMPurify.sanitize(html, {
    USE_PROFILES: { html: true },
});

export function useMarkdown() {
    const render = (markdown) => {
        if (!markdown) {
            return '';
        }

        return sanitize(marked.parse(markdown));
    };

    return { render, sanitize };
}
