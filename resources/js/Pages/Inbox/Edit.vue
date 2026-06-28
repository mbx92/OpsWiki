<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps({ item: Object });
const types = ['idea', 'error_log', 'command', 'link', 'draft_sop', 'draft_documentation', 'temporary_note'];

const form = useForm({
    title: props.item.title,
    content: props.item.content ?? '',
    type: props.item.type,
    source: props.item.source ?? '',
    priority: props.item.priority,
    tags: props.item.tags ?? [],
    status: props.item.status,
});
</script>

<template>
    <Head title="Edit Inbox Note" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Edit Note</h1>
                <FormHeaderActions
                    form-id="inbox-edit-form"
                    :cancel-href="route('inbox.show', item.id)"
                    save-label="Update"
                    :processing="form.processing"
                />
            </div>
        </template>

        <form id="inbox-edit-form" @submit.prevent="form.put(route('inbox.update', item.id))" class="mx-auto max-w-2xl space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <div>
                <InputLabel value="Title" />
                <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]" required />
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <InputLabel value="Type" />
                    <select v-model="form.type" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option v-for="t in types" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Priority" />
                    <select v-model="form.priority" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option value="low">Low</option><option value="normal">Normal</option><option value="high">High</option><option value="urgent">Urgent</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Status" />
                    <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option value="new">New</option><option value="reviewed">Reviewed</option><option value="converted">Converted</option><option value="archived">Archived</option>
                    </select>
                </div>
            </div>
            <div>
                <InputLabel value="Content" />
                <textarea v-model="form.content" rows="10" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 font-mono text-[13px] outline-none focus:border-[#111111]" />
            </div>
        </form>
    </AuthenticatedLayout>
</template>
