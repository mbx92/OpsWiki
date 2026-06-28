<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import RelatedItemsPicker from '@/Components/RelatedItemsPicker.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ troubleshootingCase: Object, tags: Array, statuses: Array, severities: Array, related: Array, linkable: Array });

const tagInput = ref('');
const form = useForm({
    title: props.troubleshootingCase.title,
    symptoms: props.troubleshootingCase.symptoms ?? '',
    environment: props.troubleshootingCase.environment ?? '',
    error_log: props.troubleshootingCase.error_log ?? '',
    suspected_causes: props.troubleshootingCase.suspected_causes ?? '',
    diagnosis_steps: props.troubleshootingCase.diagnosis_steps ?? '',
    working_solution: props.troubleshootingCase.working_solution ?? '',
    failed_attempts: props.troubleshootingCase.failed_attempts ?? '',
    validation: props.troubleshootingCase.validation ?? '',
    prevention: props.troubleshootingCase.prevention ?? '',
    severity: props.troubleshootingCase.severity,
    status: props.troubleshootingCase.status,
    tag_names: props.troubleshootingCase.tags?.map(t => t.name) ?? [],
    related: props.related?.map(r => ({ type: r.type, id: r.id })) ?? [],
});

const submit = () => form.put(route('troubleshooting.update', props.troubleshootingCase.slug));

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};
</script>

<template>
    <Head :title="`Edit: ${troubleshootingCase.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('troubleshooting.show', troubleshootingCase.slug)" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Back</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Edit Case</h1>
                </div>
                <FormHeaderActions form-id="ts-edit-form" :cancel-href="route('troubleshooting.show', troubleshootingCase.slug)" save-label="Save Changes" :processing="form.processing" />
            </div>
        </template>

        <form id="ts-edit-form" @submit.prevent="submit" class="space-y-4">
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
                <div>
                    <InputLabel value="Related items" />
                    <RelatedItemsPicker v-model="form.related" :linkable="linkable" class="mt-1.5" />
                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
