<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ tags: Array, statuses: Array, severities: Array });

const tagInput = ref('');
const form = useForm({
    title: '',
    symptoms: '',
    environment: '',
    error_log: '',
    suspected_causes: '',
    diagnosis_steps: '',
    working_solution: '',
    failed_attempts: '',
    validation: '',
    prevention: '',
    severity: 'medium',
    status: 'open',
    tag_names: [],
});

const submit = () => form.post(route('troubleshooting.store'));

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};
</script>

<template>
    <Head title="New Troubleshooting Case" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">New Case</h1>
                <FormHeaderActions form-id="ts-create-form" :cancel-href="route('troubleshooting.index')" :processing="form.processing" />
            </div>
        </template>

        <form id="ts-create-form" @submit.prevent="submit" class="space-y-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="sm:col-span-1">
                        <InputLabel value="Title" />
                        <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" required />
                    </div>
                    <div>
                        <InputLabel value="Severity" />
                        <select v-model="form.severity" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option v-for="s in severities" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
                        </select>
                    </div>
                </div>
                <div><InputLabel value="Symptoms" /><textarea v-model="form.symptoms" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Environment" /><textarea v-model="form.environment" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Error log" /><MarkdownEditor v-model="form.error_log" class="mt-1.5" /></div>
                <div><InputLabel value="Suspected causes" /><textarea v-model="form.suspected_causes" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Diagnosis steps" /><MarkdownEditor v-model="form.diagnosis_steps" class="mt-1.5" /></div>
                <div><InputLabel value="Working solution" /><MarkdownEditor v-model="form.working_solution" class="mt-1.5" /></div>
                <div><InputLabel value="Failed attempts" /><MarkdownEditor v-model="form.failed_attempts" class="mt-1.5" /></div>
                <div><InputLabel value="Validation" /><textarea v-model="form.validation" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Prevention" /><textarea v-model="form.prevention" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" /></div>
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
