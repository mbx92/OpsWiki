<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ sops: Object, filters: Object, statuses: Array });

const q = ref('');
const search = () => router.get(route('sops.index'), { q: q.value }, { preserveState: true });
</script>

<template>
    <Head title="SOPs" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">SOPs</h1>
                <div class="flex gap-2" data-page-tour="page-actions">
                    <Link :href="route('sops.import')" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">Import MD</Link>
                    <Link :href="route('sops.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">+ New SOP</Link>
                </div>
            </div>
        </template>

        <form @submit.prevent="search" class="mb-4 flex flex-wrap items-center gap-2" data-page-tour="page-filters">
            <input v-model="q" placeholder="Search SOPs..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]" />
            <Link
                v-for="s in statuses"
                :key="s"
                :href="route('sops.index', { status: s, q: filters.q })"
                class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500] capitalize"
                :class="filters.status === s ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'"
            >{{ s }}</Link>
        </form>

        <EmptyState v-if="!sops.data.length" title="No SOPs yet" description="Document step-by-step procedures with validation and rollback steps.">
            <Link :href="route('sops.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[14px] font-[600] text-white">Create SOP</Link>
        </EmptyState>

        <div v-else class="space-y-2">
            <Link
                v-for="sop in sops.data"
                :key="sop.id"
                :href="route('sops.show', sop.slug)"
                class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]"
            >
                <div>
                    <div class="text-[15px] font-[600] text-[#111111]">{{ sop.title }}</div>
                    <p v-if="sop.purpose" class="mt-0.5 line-clamp-1 text-[13px] text-[#6b7280]">{{ sop.purpose }}</p>
                </div>
                <StatusBadge :status="sop.status" />
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
