import { onMounted, watch } from 'vue';
import { useCopy } from './useCopy';

export function useWikiCodeBlocks(containerRef, contentSource) {
    const { copy } = useCopy();

    const enhance = () => {
        const el = containerRef.value;

        if (!el) {
            return;
        }

        el.querySelectorAll('.wiki-code-wrap').forEach((wrap) => {
            if (wrap.querySelector('.wiki-copy-btn')) {
                return;
            }
        });

        el.querySelectorAll('pre').forEach((pre) => {
            if (pre.closest('.wiki-code-wrap')) {
                return;
            }

            const wrap = document.createElement('div');
            wrap.className = 'wiki-code-wrap';
            pre.parentNode?.insertBefore(wrap, pre);
            wrap.appendChild(pre);

            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'wiki-copy-btn inline-flex items-center gap-1 rounded-[6px] border border-[#374151] bg-[#1a1a1a] px-2 py-1 text-[11px] font-[500] text-[#e5e7eb] hover:bg-[#242424]';
            btn.textContent = 'Copy';

            btn.addEventListener('click', async () => {
                const code = pre.querySelector('code')?.textContent ?? pre.textContent ?? '';
                await copy(code.trim());
                btn.textContent = 'Copied!';
                setTimeout(() => { btn.textContent = 'Copy'; }, 2000);
            });

            wrap.appendChild(btn);
        });
    };

    onMounted(() => setTimeout(enhance, 0));

    if (contentSource) {
        watch(contentSource, () => setTimeout(enhance, 0));
    }

    return { enhance };
}
