import { marked } from 'marked';

marked.setOptions({
    gfm: true,
    breaks: true,
});

export function useMarkdown() {
    const render = (markdown) => {
        if (!markdown) {
            return '';
        }

        return marked.parse(markdown);
    };

    return { render };
}
