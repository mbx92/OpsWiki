<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ categories: Array, tags: Array, platforms: Array });

const tagInput = ref('');
const form = useForm({
    title: '',
    description: '',
    command: '',
    language: 'bash',
    platform: null,
    category_id: null,
    is_tested: false,
    is_favorite: false,
    tag_names: [],
});

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};
</script>

<template>
    <Head title="New Snippet" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">New Snippet</h1>
                <FormHeaderActions
                    form-id="snippet-create-form"
                    :cancel-href="route('snippets.index')"
                    save-label="Save Snippet"
                    :processing="form.processing"
                />
            </div>
        </template>

        <form id="snippet-create-form" @submit.prevent="form.post(route('snippets.store'))" class="mx-auto max-w-2xl space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <div>
                <InputLabel value="Title" />
                <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" required />
            </div>
            <div>
                <InputLabel value="Description" />
                <input v-model="form.description" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel value="Platform" />
                    <select v-model="form.platform" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option :value="null">Any</option>
                        <option v-for="p in platforms" :key="p" :value="p">{{ p }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Language" />
                    <input v-model="form.language" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
            </div>
            <div>
                <InputLabel value="Command" />
                <textarea v-model="form.command" rows="8" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 font-mono text-[13px]" required />
            </div>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-[14px]"><input type="checkbox" v-model="form.is_tested" /> Tested</label>
                <label class="flex items-center gap-2 text-[14px]"><input type="checkbox" v-model="form.is_favorite" /> Favorite</label>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
