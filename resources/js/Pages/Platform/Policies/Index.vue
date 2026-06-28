<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ documents: Array });
</script>

<template>
    <Head title="Platform Policies" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Platform · Policies</h1>
        </template>

        <PlatformNav />

        <div class="space-y-3">
            <Link v-for="doc in documents" :key="doc.id" :href="route('platform.policies.edit', doc.id)" class="block rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[15px] font-[600] text-[#111111]">{{ doc.title }}</p>
                        <p class="mt-1 text-[13px] text-[#6b7280]">/{{ doc.slug }} · v{{ doc.version }}</p>
                    </div>
                    <span class="rounded-[9999px] px-2 py-0.5 text-[12px]" :class="doc.status === 'published' ? 'bg-[#ecfdf5] text-[#10b981]' : 'bg-[#f3f4f6] text-[#6b7280]'">{{ doc.status }}</span>
                </div>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
