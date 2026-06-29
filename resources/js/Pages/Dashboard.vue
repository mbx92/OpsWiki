<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    recentPages: Array,
    recentInbox: Array,
    favoriteSnippets: Array,
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Dashboard</h1>
        </template>

        <!-- Stats -->
        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4" data-tour="dashboard-stats">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="text-[13px] text-[#898989]">New Inbox</div>
                <div class="mt-1 text-[28px] font-[700] text-[#111111]">{{ stats.inbox_new }}</div>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="text-[13px] text-[#898989]">Wiki Pages</div>
                <div class="mt-1 text-[28px] font-[700] text-[#111111]">{{ stats.pages_total }}</div>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="text-[13px] text-[#898989]">Snippets</div>
                <div class="mt-1 text-[28px] font-[700] text-[#111111]">{{ stats.snippets_total }}</div>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="text-[13px] text-[#898989]">Favorites</div>
                <div class="mt-1 text-[28px] font-[700] text-[#111111]">{{ stats.snippets_favorite }}</div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="mb-6 flex flex-wrap gap-2" data-tour="dashboard-quick-actions">
            <Link :href="route('inbox.create')" class="rounded-[8px] border border-[#e5e7eb] bg-white px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">+ New Note</Link>
            <Link :href="route('wiki.create')" class="rounded-[8px] border border-[#e5e7eb] bg-white px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">+ Wiki Page</Link>
            <Link :href="route('snippets.create')" class="rounded-[8px] border border-[#e5e7eb] bg-white px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">+ Snippet</Link>
            <Link :href="route('tools.index')" class="rounded-[8px] border border-[#e5e7eb] bg-white px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">Tools</Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <!-- Recent pages -->
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-[15px] font-[600] text-[#111111]">Recent Pages</h2>
                    <Link :href="route('wiki.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">View all</Link>
                </div>
                <div v-if="recentPages.length" class="space-y-3">
                    <Link
                        v-for="page in recentPages"
                        :key="page.id"
                        :href="route('wiki.show', page.slug)"
                        class="flex items-center justify-between rounded-[8px] px-3 py-2 hover:bg-[#f8f9fa]"
                    >
                        <span class="text-[14px] font-[500] text-[#111111]">{{ page.title }}</span>
                        <StatusBadge :status="page.status" />
                    </Link>
                </div>
                <p v-else class="text-[14px] text-[#898989]">No pages yet.</p>
            </div>

            <!-- Recent inbox -->
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-[15px] font-[600] text-[#111111]">Inbox</h2>
                    <Link :href="route('inbox.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">View all</Link>
                </div>
                <div v-if="recentInbox.length" class="space-y-3">
                    <Link
                        v-for="item in recentInbox"
                        :key="item.id"
                        :href="route('inbox.show', item.id)"
                        class="flex items-center justify-between rounded-[8px] px-3 py-2 hover:bg-[#f8f9fa]"
                    >
                        <span class="text-[14px] font-[500] text-[#111111]">{{ item.title }}</span>
                        <span class="text-[12px] capitalize text-[#898989]">{{ item.type.replace('_', ' ') }}</span>
                    </Link>
                </div>
                <p v-else class="text-[14px] text-[#898989]">Inbox is clear.</p>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
