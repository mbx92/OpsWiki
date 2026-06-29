<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ cases: Object, filters: Object, statuses: Array, severities: Array });

const q = ref('');
const search = () => router.get(route('troubleshooting.index'), { q: q.value }, { preserveState: true });
</script>

<template>
    <Head title="Troubleshooting" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Troubleshooting</h1>
                <div class="flex gap-2" data-page-tour="page-actions">
                    <Link :href="route('troubleshooting.import')" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">Import MD</Link>
                    <Link :href="route('troubleshooting.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">+ New Case</Link>
                </div>
            </div>
        </template>

        <form @submit.prevent="search" class="mb-4 flex flex-wrap items-center gap-2" data-page-tour="page-filters">
            <input v-model="q" placeholder="Search cases..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]" />
            <Link
                v-for="s in statuses"
                :key="s"
                :href="route('troubleshooting.index', { status: s, q: filters.q })"
                class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500] capitalize"
                :class="filters.status === s ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'"
            >{{ s }}</Link>
        </form>

        <EmptyState v-if="!cases.data.length" title="No cases yet" description="Document problems you've solved so you don't have to solve them twice.">
            <Link :href="route('troubleshooting.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[14px] font-[600] text-white">Create case</Link>
        </EmptyState>

        <div v-else class="space-y-2">
            <Link
                v-for="item in cases.data"
                :key="item.id"
                :href="route('troubleshooting.show', item.slug)"
                class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]"
            >
                <div>
                    <div class="text-[15px] font-[600] text-[#111111]">{{ item.title }}</div>
                    <p v-if="item.symptoms" class="mt-0.5 line-clamp-1 text-[13px] text-[#6b7280]">{{ item.symptoms }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="rounded-[9999px] bg-[#f3f4f6] px-2 py-0.5 text-[12px] capitalize">{{ item.severity }}</span>
                    <StatusBadge :status="item.status" />
                </div>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
