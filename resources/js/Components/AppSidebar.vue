<script setup>
import { Link } from '@inertiajs/vue3';
import { useSidebarNav } from '@/Composables/useSidebarNav';

const { sidebarNav, isNavActive, requiredPlanLabel, upgradeUrl } = useSidebarNav();

const tourKeys = {
    'inbox.index': 'nav-inbox',
    'wiki.index': 'nav-wiki',
    'snippets.index': 'nav-snippets',
    'settings.index': 'nav-settings',
};

const icons = {
    dashboard: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
    'inbox.index': 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
    'books.index': 'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z',
    'wiki.index': 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
    'snippets.index': 'M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
    'sops.index': 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
    'troubleshooting.index': 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
    'projects.index': 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
    'knowledge.index': 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1',
    'assistant.index': 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',
    'tools.index': 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    'assets.index': 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
    'settings.index': 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
};
</script>

<template>
    <aside class="hidden w-56 shrink-0 self-start border-r border-[#e5e7eb] bg-white lg:sticky lg:top-0 lg:z-30 lg:flex lg:h-screen lg:flex-col lg:overflow-y-auto">
        <div class="flex h-16 items-center gap-2 border-b border-[#e5e7eb] px-4">
            <div class="flex h-7 w-7 items-center justify-center rounded-[6px] bg-[#111111]">
                <svg class="h-3.5 w-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <Link :href="route('dashboard')" class="text-[15px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">OpsWiki</Link>
        </div>

        <nav class="flex-1 space-y-0.5 p-3" data-tour="sidebar">
            <Link
                v-for="item in sidebarNav"
                :key="item.route"
                :href="item.locked ? upgradeUrl(item.requiredPlan, item.planFeature) : route(item.route)"
                :data-tour="tourKeys[item.route] || undefined"
                class="flex items-center gap-2.5 rounded-[8px] px-3 py-2 text-[14px] font-[500] transition-colors duration-150"
                :class="[
                    isNavActive(item) && !item.locked
                        ? 'bg-[#f0f0f0] text-[#111111] font-[600] shadow-[inset_0_0_0_1px_#e5e7eb]'
                        : 'text-[#6b7280] hover:bg-[#f8f9fa] hover:text-[#111111] active:bg-[#f0f0f0]',
                    item.locked ? 'opacity-75' : '',
                ]"
                :title="item.locked ? `${requiredPlanLabel(item.requiredPlan)} plan required` : undefined"
            >
                <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" :d="icons[item.route]"/>
                </svg>
                <span class="truncate">{{ item.name }}</span>
                <span
                    v-if="item.locked"
                    class="ml-auto shrink-0 rounded-[4px] bg-[#fef3c7] px-1.5 py-0.5 text-[10px] font-[600] uppercase tracking-wide text-[#92400e]"
                >
                    {{ requiredPlanLabel(item.requiredPlan) }}
                </span>
            </Link>
        </nav>
    </aside>
</template>
