<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ document: Object });

const form = useForm({
    title: props.document.title,
    content_markdown: props.document.content_markdown,
    status: props.document.status,
});

const submit = () => form.put(route('platform.policies.update', props.document.id));
</script>

<template>
    <Head :title="`Edit: ${document.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('platform.policies.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Policies</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ document.title }}</h1>
                </div>
                <FormHeaderActions form-id="policy-form" :cancel-href="route('platform.policies.index')" save-label="Save" :processing="form.processing" />
            </div>
        </template>

        <PlatformNav />

        <form id="policy-form" @submit.prevent="submit" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><InputLabel value="Title" /><input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" required /></div>
                <div>
                    <InputLabel value="Status" />
                    <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option value="draft">Draft</option>
                        <option value="published">Published</option>
                    </select>
                </div>
            </div>
            <div>
                <InputLabel value="Content (Markdown)" />
                <MarkdownEditor v-model="form.content_markdown" class="mt-1.5" :rows="20" />
            </div>
        </form>
    </AuthenticatedLayout>
</template>
