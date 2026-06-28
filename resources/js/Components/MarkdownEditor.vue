<script setup>
import MarkdownPreview from './MarkdownPreview.vue';
import { ref, computed } from 'vue';

const model = defineModel({ type: String, default: '' });

defineProps({
    rows: { type: Number, default: 20 },
    placeholder: { type: String, default: 'Write markdown here...' },
});

const textarea = ref(null);
const preview = ref(true);

const toolbar = [
    { label: 'H2', prefix: '## ', suffix: '', block: true },
    { label: 'H3', prefix: '### ', suffix: '', block: true },
    { label: 'B', prefix: '**', suffix: '**', wrap: true },
    { label: 'I', prefix: '*', suffix: '*', wrap: true },
    { label: 'Code', prefix: '`', suffix: '`', wrap: true },
    { label: 'Link', prefix: '[', suffix: '](url)', wrap: true },
    { label: 'List', prefix: '- ', suffix: '', line: true },
    { label: 'Quote', prefix: '> ', suffix: '', line: true },
];

const insert = (item) => {
    const el = textarea.value;
    if (!el) return;

    const start = el.selectionStart;
    const end = el.selectionEnd;
    const selected = model.value.slice(start, end) || 'text';
    let before = model.value.slice(0, start);
    let after = model.value.slice(end);

    if (item.line) {
        const lineStart = before.lastIndexOf('\n') + 1;
        before = before.slice(0, lineStart) + item.prefix + before.slice(lineStart);
    } else if (item.block && before && !before.endsWith('\n\n')) {
        before += before.endsWith('\n') ? '\n' : '\n\n';
    }

    const insertion = item.wrap
        ? `${item.prefix}${selected}${item.suffix}`
        : `${item.prefix}${selected}${item.suffix}`;

    model.value = before + insertion + after;

    requestAnimationFrame(() => {
        el.focus();
        const pos = (before + insertion).length;
        el.setSelectionRange(pos, pos);
    });
};

const onKeydown = (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key === 'b') {
        event.preventDefault();
        insert(toolbar.find(t => t.label === 'B'));
    }
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        insert(toolbar.find(t => t.label === 'Link'));
    }
    if (event.key === 'Tab') {
        event.preventDefault();
        const el = textarea.value;
        const start = el.selectionStart;
        const end = el.selectionEnd;
        model.value = model.value.slice(0, start) + '    ' + model.value.slice(end);
        requestAnimationFrame(() => el.setSelectionRange(start + 4, start + 4));
    }
};

const wordCount = computed(() => {
    const text = model.value.trim();
    return text ? text.split(/\s+/).length : 0;
});
</script>

<template>
    <div class="space-y-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap gap-1">
                <button
                    v-for="item in toolbar"
                    :key="item.label"
                    type="button"
                    class="rounded-[6px] border border-[#e5e7eb] px-2 py-1 text-[12px] font-[600] text-[#374151] hover:bg-[#f8f9fa]"
                    @click="insert(item)"
                >{{ item.label }}</button>
            </div>
            <div class="flex items-center gap-3 text-[12px] text-[#898989]">
                <span>{{ wordCount }} words</span>
                <label class="flex items-center gap-1.5">
                    <input v-model="preview" type="checkbox" class="rounded" />
                    Preview
                </label>
            </div>
        </div>

        <div :class="preview ? 'grid gap-4 lg:grid-cols-2' : ''">
            <div>
                <textarea
                    ref="textarea"
                    v-model="model"
                    :rows="rows"
                    :placeholder="placeholder"
                    class="block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 font-mono text-[13px] leading-relaxed text-[#111111] outline-none focus:border-[#111111]"
                    @keydown="onKeydown"
                />
                <p class="mt-1 text-[11px] text-[#898989]">⌘B bold · ⌘K link · Tab indent</p>
            </div>
            <div v-if="preview">
                <div class="min-h-[200px] rounded-[8px] border border-[#e5e7eb] bg-[#f8f9fa] px-4 py-3">
                    <MarkdownPreview :content="model" />
                </div>
            </div>
        </div>
    </div>
</template>
