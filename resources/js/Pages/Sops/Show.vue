<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TagBadge from '@/Components/TagBadge.vue';
import MarkdownPreview from '@/Components/MarkdownPreview.vue';
import WikiTableOfContents from '@/Components/WikiTableOfContents.vue';
import RelatedItemsList from '@/Components/RelatedItemsList.vue';
import { useWikiHeadings } from '@/Composables/useWikiHeadings';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ sop: Object, related: Array });

const sections = [
    { key: 'purpose', label: 'Purpose' },
    { key: 'use_case', label: 'Use case' },
    { key: 'requirements', label: 'Requirements' },
    { key: 'steps', label: 'Steps', markdown: true },
    { key: 'validation', label: 'Validation' },
    { key: 'rollback', label: 'Rollback' },
    { key: 'notes', label: 'Notes' },
];

const contentRef = ref(null);
const contentVersion = computed(() =>
    sections.map((s) => props.sop[s.key] ?? '').join('\n'),
);
const { headings, activeId } = useWikiHeadings(contentRef, contentVersion);
const hasToc = computed(() => headings.value.length > 0);

const destroy = async () => {
    if (await confirmDelete('This SOP will be permanently deleted.', 'Delete this SOP?')) {
        router.delete(route('sops.destroy', props.sop.slug));
    }
};

const scrollToHeading = (id) => {
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    history.replaceState(null, '', `#${id}`);
};
</script>

<template>
    <Head :title="sop.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('sops.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← SOPs</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ sop.title }}</h1>
                    <StatusBadge :status="sop.status" />
                </div>
                <div class="flex gap-2">
                    <Link :href="route('sops.edit', sop.slug)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">Edit</Link>
                    <button @click="destroy" class="rounded-[8px] border border-[#fecaca] px-3 py-1.5 text-[13px] font-[500] text-[#ef4444]">Delete</button>
                </div>
            </div>
        </template>

        <div :class="hasToc ? 'mx-auto max-w-6xl' : 'mx-auto max-w-3xl'">
            <div :class="hasToc ? 'flex flex-col gap-6 lg:flex-row' : ''">
                <WikiTableOfContents :headings="headings" :active-id="activeId" />

                <div :class="hasToc ? 'min-w-0 flex-1' : ''">
                    <div v-if="sop.tags?.length" class="mb-4 flex flex-wrap gap-2">
                        <TagBadge v-for="tag in sop.tags" :key="tag.id" :name="tag.name" />
                    </div>

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

                    <div ref="contentRef" class="space-y-4">
                        <section
                            v-for="section in sections"
                            :key="section.key"
                            v-show="sop[section.key]"
                            class="rounded-[12px] border border-[#e5e7eb] bg-white p-6"
                        >
                            <h2 :id="section.key" class="mb-3 scroll-mt-36 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">
                                {{ section.label }}
                            </h2>
                            <MarkdownPreview v-if="section.markdown" :content="sop[section.key]" />
                            <p v-else class="whitespace-pre-wrap text-[15px] text-[#374151]">{{ sop[section.key] }}</p>
                        </section>
                    </div>

                    <RelatedItemsList :items="related" class="mt-4" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
