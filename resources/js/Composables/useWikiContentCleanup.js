const TOC_HEADING = /^(daftar\s+isi|table\s+of\s+contents|contents|on\s+this\s+page|in\s+this\s+article)$/i;

const TOC_SELECTOR = [
    '[class*="table-of-contents"]',
    '[class*="table_of_contents"]',
    '[class*="daftar-isi"]',
    '[class*="daftar_isi"]',
    '[id*="table-of-contents"]',
    '[id*="table_of_contents"]',
    '[id*="daftar-isi"]',
    '[id*="daftar_isi"]',
    '.toc',
    '#toc',
    '[role="doc-toc"]',
].join(', ');

function isTocHeading(text) {
    return TOC_HEADING.test((text || '').trim());
}

function isTocList(element) {
    if (!element?.matches('ul, ol, nav, div, p, section')) {
        return false;
    }

    const links = element.querySelectorAll('a[href^="#"]');

    return links.length >= 2 || (links.length === 1 && element.querySelectorAll('li').length >= 2);
}

function isTocOnlyContainer(element) {
    if (!element?.matches('section, div')) {
        return false;
    }

    let hasTocHeading = false;
    let anchorCount = 0;
    let otherContent = false;

    for (const child of element.children) {
        if (/^H[1-6]$/.test(child.tagName)) {
            if (isTocHeading(child.textContent)) {
                hasTocHeading = true;
            } else {
                otherContent = true;
            }
            continue;
        }

        if (child.matches('a[href^="#"]')) {
            anchorCount += 1;
            continue;
        }

        if (child.matches('ul, ol, nav') && isTocList(child)) {
            anchorCount += 2;
            continue;
        }

        otherContent = true;
    }

    return hasTocHeading && anchorCount >= 2 && !otherContent;
}

function removeTocHeadingBlock(heading) {
    const parent = heading.parentElement;
    const toRemove = [heading];
    let sibling = heading.nextElementSibling;

    while (sibling) {
        if (sibling.matches('a[href^="#"]')) {
            toRemove.push(sibling);
            sibling = sibling.nextElementSibling;
            continue;
        }

        if (sibling.matches('ul, ol, nav')) {
            toRemove.push(sibling);
            sibling = sibling.nextElementSibling;
            continue;
        }

        if (sibling.matches('div, section, p') && isTocList(sibling)) {
            toRemove.push(sibling);
            sibling = sibling.nextElementSibling;
            continue;
        }

        break;
    }

    toRemove.forEach((el) => el.remove());

    if (parent?.matches('section, div') && parent.textContent.trim() === '') {
        parent.remove();
    }
}

function removeEmptyElements(root) {
    root.querySelectorAll('span, div, section, p').forEach((el) => {
        if (el.closest('.wiki-code-wrap, .wiki-table-wrap, pre, table')) {
            return;
        }

        const text = (el.textContent || '').trim();
        const hasMedia = el.querySelector('img, table, pre, svg, hr');

        if (!text && !hasMedia && el.children.length === 0) {
            el.remove();
        }
    });
}

export function removeEmbeddedToc(root) {
    if (!root) {
        return;
    }

    root.querySelectorAll(TOC_SELECTOR).forEach((el) => el.remove());

    root.querySelectorAll('section, div').forEach((el) => {
        if (isTocOnlyContainer(el)) {
            el.remove();
        }
    });

    root.querySelectorAll('h1, h2, h3, h4, h5, h6').forEach((heading) => {
        if (isTocHeading(heading.textContent)) {
            removeTocHeadingBlock(heading);
        }
    });

    removeEmptyElements(root);
}

export function isTocLikeSummary(text) {
    if (!text?.trim()) {
        return true;
    }

    const normalized = text.trim();

    if (TOC_HEADING.test(normalized)) {
        return true;
    }

    if (/^daftar\s+isi/i.test(normalized)) {
        return true;
    }

    const withoutLabel = normalized.replace(/^daftar\s+isi\s*/i, '');
    const numberedSections = withoutLabel.match(/\d+\.\s+\S+/g);

    return numberedSections !== null && numberedSections.length >= 3;
}
