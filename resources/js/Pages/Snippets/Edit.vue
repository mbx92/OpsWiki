<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, useForm, router } from '@inertiajs/vue3';

const props = defineProps({ snippet: Object, categories: Array, tags: Array, platforms: Array });

const form = useForm({
    title: props.snippet.title,
    description: props.snippet.description ?? '',
    command: props.snippet.command,
    language: props.snippet.language,
    platform: props.snippet.platform,
    category_id: props.snippet.category_id,
    is_tested: props.snippet.is_tested,
    is_favorite: props.snippet.is_favorite,
    tag_names: props.snippet.tags?.map(t => t.name) ?? [],
});

const destroy = async () => {
    if (await confirmDelete('This snippet will be permanently deleted.', 'Delete snippet?')) {
        router.delete(route('snippets.destroy', props.snippet.id));
    }
};
</script>

<template>
    <Head :title="`Edit: ${snippet.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Edit Snippet</h1>
                <div class="flex flex-wrap items-center gap-2">
                    <FormHeaderActions
                        form-id="snippet-edit-form"
                        :cancel-href="route('snippets.index')"
                        save-label="Update"
                        :processing="form.processing"
                    />
                    <button type="button" @click="destroy" class="inline-flex h-10 items-center justify-center rounded-[8px] border border-[#fecaca] px-[20px] text-[14px] font-[600] text-[#ef4444] hover:bg-[#fef2f2]">
                        Delete
                    </button>
                </div>
            </div>
        </template>

        <form id="snippet-edit-form" @submit.prevent="form.put(route('snippets.update', snippet.id))" class="mx-auto max-w-2xl space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
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
