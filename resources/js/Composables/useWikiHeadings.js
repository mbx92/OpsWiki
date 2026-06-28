import { onMounted, onUnmounted, ref, watch } from 'vue';
import { removeEmbeddedToc } from './useWikiContentCleanup';

function slugify(text) {
    const slug = text
        .toLowerCase()
        .trim()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/gi, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

    return slug || '';
}

function getContentRoot(container) {
    if (!container) {
        return null;
    }

    return container.querySelector('.wiki-content') || container;
}

export function useWikiHeadings(containerRef, contentSource) {
    const headings = ref([]);
    const activeId = ref('');
    const scrollOffset = 144;

    const extract = () => {
        const root = getContentRoot(containerRef.value);

        if (!root) {
            headings.value = [];
            activeId.value = '';
            return;
        }

        removeEmbeddedToc(root);

        const nodes = root.querySelectorAll('h2, h3, h4, h5, h6');
        const usedIds = new Set();
        const items = [];

        nodes.forEach((node, index) => {
            let id = node.id;

            if (!id) {
                const base = slugify(node.textContent || '') || `section-${index + 1}`;
                id = base;
                let suffix = 1;

                while (usedIds.has(id)) {
                    id = `${base}-${suffix++}`;
                }

                node.id = id;
            }

            usedIds.add(id);
            items.push({
                id,
                text: (node.textContent || '').trim(),
                level: parseInt(node.tagName.charAt(1), 10),
            });
        });

        headings.value = items;
        updateActiveHeading();
    };

    const updateActiveHeading = () => {
        if (!headings.value.length) {
            activeId.value = '';
            return;
        }

        let current = headings.value[0].id;

        for (const heading of headings.value) {
            const el = document.getElementById(heading.id);

            if (el && el.getBoundingClientRect().top <= scrollOffset) {
                current = heading.id;
            }
        }

        activeId.value = current;
    };

    const scheduleExtract = () => {
        setTimeout(extract, 0);
    };

    onMounted(() => {
        scheduleExtract();
        window.addEventListener('scroll', updateActiveHeading, { passive: true });
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', updateActiveHeading);
    });

    if (contentSource) {
        watch(contentSource, scheduleExtract);
    }

    return { headings, activeId, updateActiveHeading };
}
