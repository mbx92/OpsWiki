<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({ categories: Array });

const form = useForm({
    title: '',
    description: '',
    category_id: null,
    status: 'draft',
});

const submit = () => form.post(route('books.store'));
</script>

<template>
    <Head title="New Book" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('books.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Shelf</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">New Book</h1>
                </div>
                <FormHeaderActions form-id="book-create-form" :cancel-href="route('books.index')" save-label="Create Book" :processing="form.processing" />
            </div>
        </template>

        <form id="book-create-form" @submit.prevent="submit" class="mx-auto max-w-2xl space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <div>
                <InputLabel value="Title" />
                <input
                    v-model="form.title"
                    required
                    placeholder="e.g. Proxmox Guide"
                    class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]"
                />
                <p v-if="form.errors.title" class="mt-1 text-[13px] text-[#ef4444]">{{ form.errors.title }}</p>
            </div>

            <div>
                <InputLabel value="Description" />
                <textarea
                    v-model="form.description"
                    rows="3"
                    placeholder="Ringkasan singkat isi book..."
                    class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]"
                />
                <p v-if="form.errors.description" class="mt-1 text-[13px] text-[#ef4444]">{{ form.errors.description }}</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel value="Category" />
                    <select v-model="form.category_id" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option :value="null">None</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Status" />
                    <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option value="draft">Draft</option>
                        <option value="review">Review</option>
                        <option value="tested">Tested</option>
                        <option value="production">Production</option>
                        <option value="deprecated">Deprecated</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
            </div>

            <p class="text-[13px] text-[#6b7280]">
                Setelah book dibuat, tambahkan halaman lewat import MD/HTML atau pilih halaman wiki yang sudah ada.
            </p>
        </form>
    </AuthenticatedLayout>
</template>
