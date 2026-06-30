<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ tags: Array, statuses: Array });

const tagInput = ref('');
const form = useForm({
    name: '',
    description: '',
    status: 'planning',
    repository_url: '',
    production_url: '',
    staging_url: '',
    server_location: '',
    environment_notes: '',
    deployment_notes: '',
    database_notes: '',
    backup_notes: '',
    tag_names: [],
});

const submit = () => form.post(route('projects.store'));

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};

const removeTag = (name) => {
    form.tag_names = form.tag_names.filter((tag) => tag !== name);
};
</script>

<template>
    <Head title="New Project" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">New Project</h1>
                <FormHeaderActions form-id="project-create-form" :cancel-href="route('projects.index')" :processing="form.processing" />
            </div>
        </template>

        <form id="project-create-form" @submit.prevent="submit" class="space-y-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Name" />
                        <input v-model="form.name" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" required />
                    </div>
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel value="Description" />
                    <textarea v-model="form.description" rows="3" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><InputLabel value="Repository URL" /><input v-model="form.repository_url" type="url" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Server location" /><input v-model="form.server_location" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Production URL" /><input v-model="form.production_url" type="url" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                    <div><InputLabel value="Staging URL" /><input v-model="form.staging_url" type="url" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                </div>
                <div>
                    <InputLabel value="Environment notes" />
                    <textarea v-model="form.environment_notes" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" placeholder="URL, credentials vault, env vars penting..." />
                </div>
                <div class="grid gap-4 lg:grid-cols-3">
                    <div>
                        <InputLabel value="Deployment notes" />
                        <textarea v-model="form.deployment_notes" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" placeholder="Coolify, CI/CD, rollback..." />
                    </div>
                    <div>
                        <InputLabel value="Database notes" />
                        <textarea v-model="form.database_notes" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" placeholder="Host, backup schedule, migration..." />
                    </div>
                    <div>
                        <InputLabel value="Backup notes" />
                        <textarea v-model="form.backup_notes" rows="4" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" placeholder="Restore procedure, retention..." />
                    </div>
                </div>
                <div>
                    <InputLabel value="Tags" />
                    <div class="mt-1.5 flex gap-2">
                        <input v-model="tagInput" @keydown.enter.prevent="addTag" placeholder="Add tag..." class="flex-1 rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <button type="button" @click="addTag" class="rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[13px]">Add</button>
                    </div>
                    <div v-if="form.tag_names.length" class="mt-2 flex flex-wrap gap-1.5">
                        <span v-for="tag in form.tag_names" :key="tag" class="inline-flex items-center gap-1 rounded-[9999px] bg-[#f5f5f5] px-2.5 py-0.5 text-[12px]">
                            {{ tag }}
                            <button type="button" @click="removeTag(tag)" class="text-[#898989] hover:text-[#111111]">×</button>
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
