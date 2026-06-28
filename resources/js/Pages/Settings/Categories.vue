<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

defineProps({ categories: Array });

const form = useForm({ name: '', type: 'general' });

const destroy = async (id) => {
    if (await confirmDelete('This category will be permanently removed.', 'Delete category?')) {
        router.delete(route('settings.categories.destroy', id));
    }
};
</script>

<template>
    <Head title="Categories" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Categories</h1>
            </div>
        </template>

        <form @submit.prevent="form.post(route('settings.categories.store'), { onSuccess: () => form.reset() })" class="mb-6 flex gap-3 rounded-[12px] border border-[#e5e7eb] bg-white p-5">
            <div class="flex-1">
                <InputLabel value="New category" />
                <input v-model="form.name" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" placeholder="Category name" required />
            </div>
            <button type="submit" class="self-end rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white">Add</button>
        </form>

        <div class="space-y-2">
            <div v-for="cat in categories" :key="cat.id" class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3">
                <div>
                    <span class="text-[14px] font-[500] text-[#111111]">{{ cat.name }}</span>
                    <span class="ml-2 text-[13px] text-[#898989]">{{ cat.pages_count }} pages</span>
                </div>
                <button @click="destroy(cat.id)" class="text-[13px] text-[#ef4444]">Delete</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
