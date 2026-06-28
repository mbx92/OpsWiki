<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import MarkdownPreview from '@/Components/MarkdownPreview.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ item: Object });

const convertToPage = () => router.post(route('inbox.convert.page', props.item.id));
const convertToSnippet = () => router.post(route('inbox.convert.snippet', props.item.id));
const convertToSop = () => router.post(route('inbox.convert.sop', props.item.id));
const convertToTroubleshooting = () => router.post(route('inbox.convert.troubleshooting', props.item.id));
const archive = () => router.put(route('inbox.update', props.item.id), { ...props.item, status: 'archived' });
const destroy = async () => {
    if (await confirmDelete('This inbox item will be permanently deleted.', 'Delete this item?')) {
        router.delete(route('inbox.destroy', props.item.id));
    }
};
</script>

<template>
    <Head :title="item.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ item.title }}</h1>
                    <StatusBadge :status="item.status" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link :href="route('inbox.edit', item.id)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">Edit</Link>
                    <button v-if="item.status !== 'converted'" @click="convertToPage" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">→ Wiki Page</button>
                    <button v-if="item.status !== 'converted'" @click="convertToSop" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">→ SOP</button>
                    <button v-if="item.status !== 'converted'" @click="convertToTroubleshooting" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">→ Troubleshooting</button>
                    <button v-if="item.status !== 'converted'" @click="convertToSnippet" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">→ Snippet</button>
                    <button @click="archive" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">Archive</button>
                    <button @click="destroy" class="rounded-[8px] border border-[#fecaca] px-3 py-1.5 text-[13px] font-[500] text-[#ef4444]">Delete</button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-3xl rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <div class="mb-4 flex gap-4 text-[13px] text-[#898989]">
                <span class="capitalize">{{ item.type.replace('_', ' ') }}</span>
                <span>{{ item.priority }} priority</span>
                <span v-if="item.source">Source: {{ item.source }}</span>
            </div>
            <MarkdownPreview v-if="item.content" :content="item.content" />
            <p v-else class="text-[14px] text-[#898989]">No content.</p>
        </div>
    </AuthenticatedLayout>
</template>
