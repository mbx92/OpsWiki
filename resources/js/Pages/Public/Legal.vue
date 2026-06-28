<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import MarkdownPreview from '@/Components/MarkdownPreview.vue';
import { Head } from '@inertiajs/vue3';

defineProps({ document: Object });
</script>

<template>
    <Head :title="document.title" />
    <PublicLayout>
        <div class="mx-auto max-w-3xl">
            <h1 class="mb-6 text-[28px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ document.title }}</h1>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-8">
                <MarkdownPreview :content="document.content_markdown" />
            </div>
            <p class="mt-4 text-[13px] text-[#898989]">Version {{ document.version }}</p>
        </div>
    </PublicLayout>
</template>
