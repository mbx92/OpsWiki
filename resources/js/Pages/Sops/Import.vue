<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import FileDropzone from '@/Components/FileDropzone.vue';
import FolderImportButton from '@/Components/FolderImportButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const dropzoneRef = ref(null);
const selectedFiles = ref([]);

const form = useForm({
    status: 'draft',
    files: [],
});

const applyFiles = (files) => {
    const list = Array.isArray(files) ? files : (files ? [files] : []);
    selectedFiles.value = list.filter((file) => /\.(md|markdown|zip)$/i.test(file.name));
    form.files = selectedFiles.value;
};

const onFolderSelected = (files) => {
    applyFiles(files.filter((file) => /\.(md|markdown)$/i.test(file.name)));
    dropzoneRef.value?.clear();
};

const submit = () => {
    form.post(route('sops.import.store'), {
        forceFormData: true,
        onSuccess: () => {
            selectedFiles.value = [];
            form.files = [];
            dropzoneRef.value?.clear();
        },
    });
};
</script>

<template>
    <Head title="Import SOP" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('sops.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← SOPs</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Import SOP (.md)</h1>
                </div>
                <FormHeaderActions
                    form-id="sop-import-form"
                    :cancel-href="route('sops.index')"
                    save-label="Import"
                    :processing="form.processing"
                    :save-disabled="!selectedFiles.length"
                />
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <p class="mb-6 text-[14px] text-[#6b7280]">
                Upload file markdown dengan section <code class="rounded bg-[#f5f5f5] px-1">##</code>.
                Setiap file menjadi satu SOP.
            </p>

            <form id="sop-import-form" @submit.prevent="submit" class="space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div>
                    <InputLabel value="Status" />
                    <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                        <option value="draft">Draft</option>
                        <option value="review">Review</option>
                        <option value="tested">Tested</option>
                        <option value="production">Production</option>
                    </select>
                </div>

                <div>
                    <InputLabel value="Files (.md or .zip)" />
                    <FileDropzone
                        ref="dropzoneRef"
                        multiple
                        accept=".md,.markdown,.zip"
                        :max-filesize="20"
                        hint="Tarik file markdown atau zip ke sini"
                        browse-label="klik untuk pilih file"
                        :disabled="form.processing"
                        :error="form.errors.files"
                        class="mt-1.5"
                        @update:model-value="applyFiles"
                    />
                </div>

                <div>
                    <InputLabel value="Or import a folder" />
                    <div class="mt-1.5">
                        <FolderImportButton label="Pilih folder lokal" @select="onFolderSelected" />
                    </div>
                </div>

                <ul v-if="selectedFiles.length && selectedFiles[0]?.webkitRelativePath" class="space-y-1">
                    <li v-for="file in selectedFiles" :key="file.webkitRelativePath + file.size" class="text-[13px] text-[#6b7280]">
                        {{ file.webkitRelativePath }}
                    </li>
                </ul>

                <div class="rounded-[8px] bg-[#f8f9fa] p-4 text-[13px] text-[#6b7280]">
                    <p class="font-[600] text-[#374151]">Format markdown:</p>
                    <pre class="mt-2 overflow-x-auto text-[12px] leading-relaxed"># SOP: Nama SOP

## Tujuan
...

## Langkah-langkah
...

## Validasi
...</pre>
                    <p class="mt-2">Section yang dikenali: Tujuan, Use case, Requirements, Langkah, Validasi, Rollback, Notes (ID/EN).</p>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
