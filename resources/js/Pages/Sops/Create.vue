<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import LinkProjectBanner from '@/Components/LinkProjectBanner.vue';
import ImportMdHtmlPrefill from '@/Components/ImportMdHtmlPrefill.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tags: Array,
    statuses: Array,
    linkProject: Object,
    prefill: { type: Object, default: () => ({}) },
});

const tagInput = ref('');
const form = useForm({
    title: props.prefill.title ?? '',
    purpose: props.prefill.purpose ?? '',
    use_case: props.prefill.use_case ?? '',
    requirements: props.prefill.requirements ?? '',
    steps: props.prefill.steps ?? '',
    validation: props.prefill.validation ?? '',
    rollback: props.prefill.rollback ?? '',
    notes: props.prefill.notes ?? '',
    status: props.prefill.status ?? 'draft',
    tag_names: props.prefill.tag_names ?? [],
    link_project: props.linkProject?.slug ?? '',
});

const submit = () => form.post(route('sops.store'));

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};

const applyImport = (data) => {
    Object.entries(data).forEach(([key, value]) => {
        if (value !== null && value !== undefined && Object.prototype.hasOwnProperty.call(form, key)) {
            form[key] = value;
        }
    });
};
</script>

<template>
    <Head title="New SOP" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">New SOP</h1>
                <FormHeaderActions form-id="sop-create-form" :cancel-href="route('sops.index')" :processing="form.processing" />
            </div>
        </template>

        <form id="sop-create-form" @submit.prevent="submit" class="space-y-4">
            <LinkProjectBanner :link-project="linkProject" />
            <ImportMdHtmlPrefill
                type="sop"
                accept=".md,.markdown"
                hint="Upload file Markdown — lihat contoh format di bawah."
                @parsed="applyImport"
            />
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Title" />
                        <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" required />
                    </div>
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel value="Purpose" />
                    <textarea v-model="form.purpose" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Use case" />
                    <textarea v-model="form.use_case" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Requirements" />
                    <textarea v-model="form.requirements" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Steps" />
                    <MarkdownEditor v-model="form.steps" class="mt-1.5" />
                </div>
                <div>
                    <InputLabel value="Validation" />
                    <textarea v-model="form.validation" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Rollback" />
                    <textarea v-model="form.rollback" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Notes" />
                    <textarea v-model="form.notes" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Tags" />
                    <div class="mt-1.5 flex gap-2">
                        <input v-model="tagInput" @keydown.enter.prevent="addTag" placeholder="Add tag..." class="flex-1 rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <button type="button" @click="addTag" class="rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[13px]">Add</button>
                    </div>
                    <div v-if="form.tag_names.length" class="mt-2 flex flex-wrap gap-2">
                        <span v-for="tag in form.tag_names" :key="tag" class="rounded-[9999px] bg-[#f3f4f6] px-2 py-0.5 text-[12px]">{{ tag }}</span>
                    </div>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
