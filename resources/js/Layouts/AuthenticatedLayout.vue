<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppSidebar from '@/Components/AppSidebar.vue';
import GlobalSearch from '@/Components/GlobalSearch.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { useSidebarNav } from '@/Composables/useSidebarNav';

const mobileOpen = ref(false);
const { sidebarNav: navItems, isNavActive, requiredPlanLabel, upgradeUrl } = useSidebarNav();
</script>

<template>
    <div class="flex min-h-screen bg-[#f8f9fa]">
        <AppSidebar />

        <div class="flex flex-1 flex-col min-w-0">
            <!-- Topbar -->
            <header class="sticky top-0 z-40 flex h-16 items-center gap-4 border-b border-[#e5e7eb] bg-white px-4 lg:px-6">
                <button
                    @click="mobileOpen = !mobileOpen"
                    class="rounded-[8px] p-2 text-[#6b7280] hover:bg-[#f8f9fa] lg:hidden"
                >
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                <GlobalSearch />

                <div class="ml-auto flex items-center gap-2">
                    <Link
                        :href="route('inbox.create')"
                        class="hidden rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa] sm:inline-flex"
                    >
                        + Note
                    </Link>
                    <Link
                        :href="route('wiki.create')"
                        class="rounded-[8px] bg-[#111111] px-3 py-1.5 text-[13px] font-[600] text-white hover:bg-[#242424]"
                    >
                        + Page
                    </Link>

                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button class="flex h-8 w-8 items-center justify-center rounded-full bg-[#f5f5f5] text-[13px] font-[600] text-[#111111]">
                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                            </button>
                        </template>
                        <template #content>
                            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
                            <DropdownLink :href="route('settings.index')">Settings</DropdownLink>
                            <DropdownLink v-if="$page.props.auth.user?.is_super_admin" :href="route('platform.dashboard')">Platform Admin</DropdownLink>
                            <DropdownLink :href="route('logout')" method="post" as="button">Log Out</DropdownLink>
                        </template>
                    </Dropdown>
                </div>
            </header>

            <!-- Mobile nav -->
            <div v-if="mobileOpen" class="border-b border-[#e5e7eb] bg-white p-3 lg:hidden">
                <Link
                    v-for="item in navItems"
                    :key="item.route"
                    :href="item.locked ? upgradeUrl(item.requiredPlan, item.planFeature) : route(item.route)"
                    class="flex items-center gap-2 rounded-[8px] px-3 py-2 text-[14px] font-[500] transition-colors"
                    :class="[
                        isNavActive(item) && !item.locked ? 'bg-[#f0f0f0] text-[#111111] font-[600]' : 'text-[#374151] hover:bg-[#f8f9fa]',
                        item.locked ? 'opacity-75' : '',
                    ]"
                    @click="mobileOpen = false"
                >
                    <span class="truncate">{{ item.name }}</span>
                    <span
                        v-if="item.locked"
                        class="ml-auto shrink-0 rounded-[4px] bg-[#fef3c7] px-1.5 py-0.5 text-[10px] font-[600] uppercase text-[#92400e]"
                    >
                        {{ requiredPlanLabel(item.requiredPlan) }}
                    </span>
                </Link>
            </div>

            <!-- Page header -->
            <div v-if="$slots.header" class="sticky top-16 z-30 border-b border-[#e5e7eb] bg-white px-4 py-4 lg:px-6">
                <slot name="header" />
            </div>

            <!-- Content -->
            <main class="flex-1 p-4 lg:p-6">
                <slot />
            </main>
        </div>
    </div>
</template>
