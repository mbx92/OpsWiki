<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ page: Object, versions: Array });

const formatDate = (value) => new Date(value).toLocaleString();
</script>

<template>
    <Head :title="`History: ${page.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('wiki.show', page.slug)" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← {{ page.title }}</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Version History</h1>
            </div>
        </template>

        <div class="mx-auto max-w-2xl space-y-2">
            <p v-if="!versions.length" class="text-[14px] text-[#898989]">No saved versions yet. Versions are created when you save changes.</p>
            <div
                v-for="version in versions"
                :key="version.id"
                class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4"
            >
                <div>
                    <div class="text-[14px] font-[600] text-[#111111]">v{{ version.version_number }} — {{ version.title }}</div>
                    <div class="mt-0.5 text-[13px] text-[#898989]">
                        {{ formatDate(version.created_at) }}
                        <span v-if="version.author"> · {{ version.author.name }}</span>
                    </div>
                </div>
                <Link :href="route('wiki.versions.show', [page.slug, version.id])" class="text-[13px] font-[500] text-[#111111] hover:underline">View</Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
