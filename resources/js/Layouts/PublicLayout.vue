<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();

const tenant = computed(() => page.props.tenant);

const homeHref = computed(() => {
    if (tenant.value?.slug) {
        return route('portal.index');
    }

    return route('home');
});

const brandLabel = computed(() => tenant.value?.name ?? 'Wiki');
</script>

<template>
    <div class="min-h-screen bg-[#f8f9fa]">
        <header class="sticky top-0 z-40 border-b border-[#e5e7eb] bg-white">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 lg:px-6">
                <Link :href="homeHref" class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-[8px] bg-[#111111]">
                        <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <span style="font-family: 'Manrope', 'Inter', sans-serif; font-weight: 700;" class="text-[15px] text-[#111111]">{{ brandLabel }}</span>
                </Link>
                <Link
                    v-if="$page.props.auth?.user"
                    :href="route('dashboard')"
                    class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]"
                >
                    Dashboard
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="rounded-[8px] bg-[#111111] px-3 py-1.5 text-[13px] font-[600] text-white hover:bg-[#242424]"
                >
                    Sign in
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-8 lg:px-6">
            <div
                v-if="$slots.header"
                class="sticky top-16 z-30 -mx-4 mb-6 border-b border-[#e5e7eb] bg-[#f8f9fa] px-4 py-4 lg:-mx-6 lg:px-6"
            >
                <slot name="header" />
            </div>
            <slot />
        </main>
    </div>
</template>
