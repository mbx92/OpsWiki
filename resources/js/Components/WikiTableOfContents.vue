<script setup>
import { computed } from 'vue';

const props = defineProps({
    headings: { type: Array, default: () => [] },
    activeId: { type: String, default: '' },
});

const indentClass = (level) => {
    const map = {
        2: 'pl-2',
        3: 'pl-5',
        4: 'pl-8',
        5: 'pl-10',
        6: 'pl-12',
    };

    return map[level] || 'pl-2';
};

const hasHeadings = computed(() => props.headings.length > 0);

const scrollTo = (id) => {
    const el = document.getElementById(id);

    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        history.replaceState(null, '', `#${id}`);
    }
};
</script>

<template>
    <aside
        v-if="hasHeadings"
        class="hidden shrink-0 self-start lg:sticky lg:top-[9rem] lg:z-10 lg:block lg:max-h-[calc(100vh-9rem)] lg:w-52 lg:overflow-y-auto xl:w-56"
    >
        <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-4">
            <h2 class="mb-3 text-[13px] font-[600] uppercase tracking-wider text-[#898989]">On this page</h2>
            <nav class="space-y-0.5">
                <button
                    v-for="heading in headings"
                    :key="heading.id"
                    type="button"
                    :class="[
                        'block w-full rounded-[6px] py-1.5 text-left text-[13px] leading-snug transition-colors',
                        indentClass(heading.level),
                        activeId === heading.id
                            ? 'bg-[#f5f5f5] font-[600] text-[#111111]'
                            : 'text-[#6b7280] hover:bg-[#f8f9fa] hover:text-[#111111]',
                    ]"
                    @click="scrollTo(heading.id)"
                >
                    {{ heading.text }}
                </button>
            </nav>
        </div>
    </aside>
</template>
