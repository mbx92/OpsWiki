<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import FileDropzone from '@/Components/FileDropzone.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ assets: Object, filters: Object });

const form = useForm({ file: null });
const dropzoneRef = ref(null);
const hasFile = ref(false);
const q = ref(props.filters.q ?? '');

const onFileSelected = (file) => {
    form.file = file;
    hasFile.value = !!file;
};

const upload = () => {
    if (!form.file) {
        return;
    }

    form.post(route('assets.store'), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            hasFile.value = false;
            dropzoneRef.value?.clear();
        },
    });
};

const destroy = async (id) => {
    if (await confirmDelete('This file will be permanently deleted.', 'Delete this file?')) {
        router.delete(route('assets.destroy', id));
    }
};

const formatSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
};

const isImage = (mime) => mime?.startsWith('image/');

const search = () => router.get(route('assets.index'), { ...props.filters, q: q.value }, { preserveState: true });
</script>

<template>
    <Head title="Assets" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Assets</h1>
        </template>

        <form @submit.prevent="upload" class="mb-4 space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-5" data-page-tour="page-actions">
            <div>
                <label class="block text-[14px] font-[500] text-[#111111]">Upload file</label>
                <FileDropzone
                    ref="dropzoneRef"
                    accept=".jpg,.jpeg,.png,.gif,.webp,.svg,.pdf,.txt,.md,.zip,.tar,.gz,.yml,.yaml,.json,.conf"
                    :max-filesize="10"
                    hint="Tarik file ke sini"
                    browse-label="klik untuk pilih file"
                    :disabled="form.processing"
                    :error="form.errors.file"
                    class="mt-1.5"
                    @update:model-value="onFileSelected"
                />
            </div>
            <div class="flex justify-end">
                <button type="submit" :disabled="form.processing || !hasFile" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:cursor-not-allowed disabled:opacity-50">Upload</button>
            </div>
        </form>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <form @submit.prevent="search">
                <input v-model="q" placeholder="Search files..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]" />
            </form>
            <Link :href="route('assets.index', { mime: 'image', q: filters.q })" class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500]" :class="filters.mime === 'image' ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'">Images</Link>
            <Link :href="route('assets.index', { mime: 'document', q: filters.q })" class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500]" :class="filters.mime === 'document' ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'">Documents</Link>
            <Link :href="route('assets.index', { linked: 1, q: filters.q })" class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500]" :class="filters.linked ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'">Linked to pages</Link>
            <Link :href="route('assets.index')" class="inline-flex items-center rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[13px] font-[500] text-[#374151]">All</Link>
        </div>

        <div v-if="assets.data.length" class="overflow-hidden rounded-[12px] border border-[#e5e7eb] bg-white">
            <table class="w-full text-[14px]">
                <thead class="border-b border-[#e5e7eb] bg-[#f8f9fa] text-left text-[13px] text-[#898989]">
                    <tr>
                        <th class="px-5 py-3 font-[500]">Preview</th>
                        <th class="px-5 py-3 font-[500]">Name</th>
                        <th class="px-5 py-3 font-[500]">Size</th>
                        <th class="px-5 py-3 font-[500]">Linked</th>
                        <th class="px-5 py-3 font-[500]"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="asset in assets.data" :key="asset.id" class="border-b border-[#e5e7eb] last:border-0">
                        <td class="px-5 py-3">
                            <img v-if="isImage(asset.mime_type) && asset.url" :src="asset.url" :alt="asset.original_name" class="h-10 w-10 rounded-[6px] object-cover" />
                            <span v-else class="inline-flex h-10 w-10 items-center justify-center rounded-[6px] bg-[#f3f4f6] text-[11px] uppercase text-[#898989]">{{ asset.mime_type?.split('/')[1]?.slice(0, 4) ?? 'file' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <a v-if="asset.url" :href="asset.url" target="_blank" class="font-[500] text-[#111111] hover:underline">{{ asset.original_name }}</a>
                            <span v-else>{{ asset.original_name }}</span>
                            <div class="text-[12px] text-[#898989]">{{ asset.mime_type }}</div>
                        </td>
                        <td class="px-5 py-3 text-[#6b7280]">{{ formatSize(asset.size) }}</td>
                        <td class="px-5 py-3 text-[#6b7280]">{{ asset.related_id ? 'Wiki page' : '—' }}</td>
                        <td class="px-5 py-3 text-right">
                            <button @click="destroy(asset.id)" class="text-[13px] text-[#ef4444] hover:underline">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p v-else class="text-[14px] text-[#898989]">No assets uploaded yet.</p>
    </AuthenticatedLayout>
</template>
