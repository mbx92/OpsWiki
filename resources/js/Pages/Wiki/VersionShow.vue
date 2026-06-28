<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import MarkdownPreview from '@/Components/MarkdownPreview.vue';
import HtmlContent from '@/Components/HtmlContent.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ page: Object, version: Object });

const restore = () => {
    router.post(route('wiki.versions.restore', [props.page.slug, props.version.id]));
};
</script>

<template>
    <Head :title="`v${version.version_number}: ${page.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('wiki.history', page.slug)" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← History</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Version {{ version.version_number }}</h1>
                    <StatusBadge :status="version.status" />
                </div>
                <button @click="restore" class="rounded-[8px] bg-[#111111] px-3 py-1.5 text-[13px] font-[600] text-white hover:bg-[#242424]">Restore this version</button>
            </div>
        </template>

        <div class="mx-auto max-w-3xl rounded-[12px] border border-[#e5e7eb] bg-white p-8">
            <h2 class="mb-4 text-[22px] font-[700] text-[#111111]">{{ version.title }}</h2>
            <p v-if="version.summary" class="mb-4 text-[15px] text-[#6b7280]">{{ version.summary }}</p>
            <MarkdownPreview v-if="version.content_markdown" :content="version.content_markdown" />
            <HtmlContent v-else-if="version.content_html" :html="version.content_html" />
        </div>
    </AuthenticatedLayout>
</template>
