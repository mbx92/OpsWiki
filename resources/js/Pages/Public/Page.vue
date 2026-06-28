<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import MarkdownPreview from '@/Components/MarkdownPreview.vue';
import HtmlContent from '@/Components/HtmlContent.vue';
import WikiTableOfContents from '@/Components/WikiTableOfContents.vue';
import { useWikiHeadings } from '@/Composables/useWikiHeadings';
import { isTocLikeSummary } from '@/Composables/useWikiContentCleanup';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ page: Object });

const showBookBack = computed(() => props.page.book?.visibility === 'public');

const contentRef = ref(null);
const contentSource = computed(() => props.page.content_markdown || props.page.content_html || '');
const { headings, activeId } = useWikiHeadings(contentRef, contentSource);
const hasToc = computed(() => headings.value.length > 0);

const displaySummary = computed(() => {
    const summary = props.page.summary?.trim();

    if (!summary || isTocLikeSummary(summary)) {
        return null;
    }

    return summary;
});

const scrollToHeading = (id) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    history.replaceState(null, '', `#${id}`);
};
</script>

<template>
    <Head :title="page.title" />
    <PublicLayout>
        <template #header>
            <Link :href="route('portal.index')" class="text-[13px] text-[#6b7280] transition-colors hover:text-[#111111]">← Docs</Link>
            <Link
                v-if="showBookBack"
                :href="route('share.books.show', page.book.slug)"
                class="mt-1 block text-[13px] text-[#6b7280] transition-colors hover:text-[#111111]"
            >
                {{ page.book.title }}
            </Link>
            <h1
                class="text-[20px] font-[700] text-[#111111]"
                :class="showBookBack ? 'mt-1' : ''"
                style="font-family: 'Manrope', sans-serif;"
            >
                {{ page.title }}
            </h1>
        </template>

        <div :class="hasToc ? 'max-w-6xl' : 'max-w-3xl'">
            <div :class="hasToc ? 'flex flex-col gap-6 lg:flex-row' : ''">
                <WikiTableOfContents :headings="headings" :active-id="activeId" />

                <div :class="hasToc ? 'min-w-0 flex-1' : ''">
                    <div v-if="displaySummary" class="mb-4 text-[15px] text-[#6b7280]">{{ displaySummary }}</div>

                    <details v-if="hasToc" class="mb-4 rounded-[12px] border border-[#e5e7eb] bg-white lg:hidden">
                        <summary class="cursor-pointer px-4 py-3 text-[13px] font-[600] text-[#111111]">On this page</summary>
                        <nav class="space-y-0.5 border-t border-[#e5e7eb] px-2 py-2">
                            <button
                                v-for="heading in headings"
                                :key="heading.id"
                                type="button"
                                class="block w-full rounded-[6px] py-1.5 text-left text-[13px] text-[#6b7280] hover:bg-[#f8f9fa] hover:text-[#111111]"
                                :style="{ paddingLeft: `${(heading.level - 1) * 12 + 8}px` }"
                                @click="scrollToHeading(heading.id)"
                            >
                                {{ heading.text }}
                            </button>
                        </nav>
                    </details>

                    <div ref="contentRef" class="rounded-[12px] border border-[#e5e7eb] bg-white p-8">
                        <MarkdownPreview v-if="page.content_markdown" :content="page.content_markdown" />
                        <HtmlContent v-else-if="page.content_html" :html="page.content_html" />
                        <p v-else class="text-[14px] text-[#898989]">No content.</p>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
