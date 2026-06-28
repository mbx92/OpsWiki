<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { usePlanFeatures } from '@/Composables/usePlanFeatures';

defineProps({ tools: Array });

const { requiredPlanLabel, upgradeUrl } = usePlanFeatures();
</script>

<template>
    <Head title="Tools" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Tools</h1>
        </template>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="tool in tools"
                :key="tool.id"
                :href="tool.locked ? upgradeUrl(tool.required_plan ?? 'pro', 'tools') : route('tools.show', tool.slug)"
                class="rounded-[12px] border border-[#e5e7eb] bg-white p-6"
                :class="tool.locked ? 'opacity-70' : 'hover:bg-[#f8f9fa]'"
            >
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-[8px]" :class="tool.locked ? 'bg-[#e5e7eb]' : 'bg-[#111111]'">
                    <svg class="h-5 w-5" :class="tool.locked ? 'text-[#6b7280]' : 'text-white'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    </svg>
                </div>
                <h2 class="text-[16px] font-[600] text-[#111111]">{{ tool.title }}</h2>
                <p class="mt-1 text-[13px] text-[#6b7280]">{{ tool.description }}</p>
                <span v-if="tool.locked" class="mt-3 inline-block rounded-[8px] bg-[#fef3c7] px-2.5 py-1 text-[12px] font-[500] text-[#92400e]">
                    Upgrade to {{ requiredPlanLabel(tool.required_plan) }} →
                </span>
                <span v-else class="mt-3 inline-block text-[12px] capitalize text-[#898989]">{{ tool.tool_type }}</span>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
