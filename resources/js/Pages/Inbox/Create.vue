<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    content: '',
    type: 'idea',
    source: '',
    priority: 'normal',
    tags: [],
});

const types = ['idea', 'error_log', 'command', 'link', 'draft_sop', 'draft_documentation', 'temporary_note'];
</script>

<template>
    <Head title="New Inbox Note" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">New Note</h1>
                <FormHeaderActions
                    form-id="inbox-create-form"
                    :cancel-href="route('inbox.index')"
                    :processing="form.processing"
                />
            </div>
        </template>

        <form id="inbox-create-form" @submit.prevent="form.post(route('inbox.store'))" class="mx-auto max-w-2xl space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <div>
                <InputLabel value="Title" />
                <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]" required />
                <InputError class="mt-1" :message="form.errors.title" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel value="Type" />
                    <select v-model="form.type" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]">
                        <option v-for="t in types" :key="t" :value="t">{{ t.replace('_', ' ') }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Priority" />
                    <select v-model="form.priority" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]">
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div>
                <InputLabel value="Content" />
                <textarea v-model="form.content" rows="8" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 font-mono text-[13px] outline-none focus:border-[#111111]" placeholder="Paste error log, command, or notes..." />
            </div>
        </form>
    </AuthenticatedLayout>
</template>
