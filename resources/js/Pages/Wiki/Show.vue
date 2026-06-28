<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TagBadge from '@/Components/TagBadge.vue';
import MarkdownPreview from '@/Components/MarkdownPreview.vue';
import HtmlContent from '@/Components/HtmlContent.vue';
import WikiTableOfContents from '@/Components/WikiTableOfContents.vue';
import ShareControls from '@/Components/ShareControls.vue';
import RelatedItemsList from '@/Components/RelatedItemsList.vue';
import { useWikiHeadings } from '@/Composables/useWikiHeadings';
import { isTocLikeSummary } from '@/Composables/useWikiContentCleanup';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ page: Object, publicUrl: String, related: Array });

const backHref = computed(() => (
    props.page.book ? route('books.show', props.page.book.slug) : route('wiki.index')
));
const backLabel = computed(() => (props.page.book ? props.page.book.title : 'Wiki'));

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

const destroy = async () => {
    if (await confirmDelete('This page will be permanently deleted.', 'Delete this page?')) {
        router.delete(route('wiki.destroy', props.page.slug));
    }
};

const scrollToHeading = (id) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    history.replaceState(null, '', `#${id}`);
};
</script>

<template>
    <Head :title="page.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="backHref" class="text-[13px] text-[#6b7280] transition-colors hover:text-[#111111]">← {{ backLabel }}</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ page.title }}</h1>
                    <StatusBadge :status="page.status" />
                </div>
                <div class="flex gap-2">
                    <Link :href="route('wiki.history', page.slug)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">History</Link>
                    <a :href="route('wiki.export', page.slug)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">Export</a>
                    <Link :href="route('wiki.edit', page.slug)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">Edit</Link>
                    <button @click="destroy" class="rounded-[8px] border border-[#fecaca] px-3 py-1.5 text-[13px] font-[500] text-[#ef4444]">Delete</button>
                </div>
            </div>
        </template>

        <div :class="hasToc ? 'mx-auto max-w-6xl' : 'mx-auto max-w-3xl'">
            <ShareControls
                class="mb-6"
                :visibility="page.visibility ?? 'private'"
                :public-url="publicUrl"
                :share-path="route('share.pages.show', page.slug)"
                :update-route="route('wiki.sharing', page.slug)"
            />

            <div :class="hasToc ? 'flex flex-col gap-6 lg:flex-row' : ''">
                <WikiTableOfContents :headings="headings" :active-id="activeId" />

                <div :class="hasToc ? 'min-w-0 flex-1' : ''">
                    <div v-if="displaySummary" class="mb-4 text-[15px] text-[#6b7280]">{{ displaySummary }}</div>
                    <div v-if="page.tags?.length || page.category" class="mb-4 flex flex-wrap gap-2">
                        <TagBadge v-for="tag in page.tags" :key="tag.id" :name="tag.name" />
                        <span v-if="page.category" class="text-[13px] text-[#898989]">{{ page.category.name }}</span>
                    </div>

                    <!-- Mobile TOC -->
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

                    <RelatedItemsList :items="related" class="mt-4" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
