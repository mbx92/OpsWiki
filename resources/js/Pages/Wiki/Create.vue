<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ categories: Array, tags: Array });

const tagInput = ref('');
const form = useForm({
    title: '',
    slug: '',
    summary: '',
    content_markdown: '',
    category_id: null,
    status: 'draft',
    visibility: 'private',
    tag_names: [],
});

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};

const removeTag = (name) => {
    form.tag_names = form.tag_names.filter(t => t !== name);
};
</script>

<template>
    <Head title="New Wiki Page" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('wiki.index')" class="text-[13px] text-[#6b7280] transition-colors hover:text-[#111111]">← Wiki</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">New Wiki Page</h1>
                </div>
                <FormHeaderActions
                    form-id="wiki-create-form"
                    :cancel-href="route('wiki.index')"
                    save-label="Create Page"
                    :processing="form.processing"
                />
            </div>
        </template>

        <form id="wiki-create-form" @submit.prevent="form.post(route('wiki.store'))" class="space-y-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Title" />
                        <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]" required />
                    </div>
                    <div>
                        <InputLabel value="Category" />
                        <select v-model="form.category_id" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option :value="null">None</option>
                            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel value="Summary" />
                    <input v-model="form.summary" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option value="draft">Draft</option><option value="review">Review</option><option value="tested">Tested</option><option value="production">Production</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Visibility" />
                        <select v-model="form.visibility" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option value="private">Private</option>
                            <option value="internal">Internal</option>
                            <option value="public">Public</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel value="Tags" />
                    <div class="mt-1.5 flex gap-2">
                        <input v-model="tagInput" @keydown.enter.prevent="addTag" placeholder="Add tag..." class="flex-1 rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <button type="button" @click="addTag" class="rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[13px]">Add</button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span v-for="tag in form.tag_names" :key="tag" class="inline-flex items-center gap-1 rounded-[9999px] bg-[#f5f5f5] px-2.5 py-0.5 text-[12px]">
                            {{ tag }}
                            <button type="button" @click="removeTag(tag)" class="text-[#898989] hover:text-[#111111]">×</button>
                        </span>
                    </div>
                </div>
            </div>

            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <MarkdownEditor v-model="form.content_markdown" :rows="24" />
            </div>
        </form>
    </AuthenticatedLayout>
</template>
