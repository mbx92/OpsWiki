<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    items: Object,
    filters: Object,
});

const types = ['idea', 'error_log', 'command', 'link', 'draft_sop', 'draft_documentation', 'temporary_note'];
const statuses = ['new', 'reviewed', 'converted', 'archived'];
</script>

<template>
    <Head title="Inbox" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Inbox</h1>
                <Link :href="route('inbox.create')" data-page-tour="page-actions" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">+ New Note</Link>
            </div>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2" data-page-tour="page-filters">
            <Link :href="route('inbox.index')" class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500]" :class="!filters.status ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'">All</Link>
            <Link v-for="s in statuses" :key="s" :href="route('inbox.index', { status: s })" class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500] capitalize" :class="filters.status === s ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'">{{ s }}</Link>
        </div>

        <EmptyState v-if="!items.data.length" title="Inbox is empty" description="Capture ideas, errors, and commands before they get lost.">
            <Link :href="route('inbox.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[14px] font-[600] text-white">Create first note</Link>
        </EmptyState>

        <div v-else class="space-y-2">
            <Link
                v-for="item in items.data"
                :key="item.id"
                :href="route('inbox.show', item.id)"
                class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]"
            >
                <div>
                    <div class="text-[15px] font-[600] text-[#111111]">{{ item.title }}</div>
                    <div class="mt-0.5 text-[13px] capitalize text-[#898989]">{{ item.type.replace('_', ' ') }} · {{ item.priority }}</div>
                </div>
                <StatusBadge :status="item.status" />
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
