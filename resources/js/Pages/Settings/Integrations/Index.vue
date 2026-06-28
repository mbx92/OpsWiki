<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Integrations" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Integrations</h1>
            </div>
        </template>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link :href="route('settings.integrations.minio')" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 hover:bg-[#f8f9fa]">
                <h2 class="text-[16px] font-[600] text-[#111111]">MinIO Archive</h2>
                <p class="mt-1 text-[13px] text-[#6b7280]">Store import files and exports in an S3-compatible bucket.</p>
            </Link>
            <Link :href="route('settings.integrations.glitchtip')" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 hover:bg-[#f8f9fa]">
                <h2 class="text-[16px] font-[600] text-[#111111]">GlitchTip</h2>
                <p class="mt-1 text-[13px] text-[#6b7280]">Track errors and exceptions with Sentry-compatible monitoring.</p>
            </Link>
            <Link :href="route('settings.integrations.ai')" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 hover:bg-[#f8f9fa]">
                <h2 class="text-[16px] font-[600] text-[#111111]">AI Assistant</h2>
                <p class="mt-1 text-[13px] text-[#6b7280]">Connect OpenAI-compatible API for internal wiki assistant.</p>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
