<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TagBadge from '@/Components/TagBadge.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

defineProps({ tags: Array });

const form = useForm({ name: '', color: null });

const destroy = async (id) => {
    if (await confirmDelete('This tag will be permanently removed.', 'Delete tag?')) {
        router.delete(route('settings.tags.destroy', id));
    }
};
</script>

<template>
    <Head title="Tags" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Tags</h1>
            </div>
        </template>

        <form @submit.prevent="form.post(route('settings.tags.store'), { onSuccess: () => form.reset() })" class="mb-6 flex gap-3 rounded-[12px] border border-[#e5e7eb] bg-white p-5">
            <div class="flex-1">
                <InputLabel value="New tag" />
                <input v-model="form.name" class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989]" placeholder="Tag name" required />
            </div>
            <button type="submit" class="self-end rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white">Add</button>
        </form>

        <div class="flex flex-wrap gap-2">
            <div v-for="tag in tags" :key="tag.id" class="flex items-center gap-2 rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2">
                <TagBadge :name="tag.name" />
                <button @click="destroy(tag.id)" class="text-[12px] text-[#ef4444]">×</button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
