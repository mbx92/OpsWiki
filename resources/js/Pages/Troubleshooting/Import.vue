<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import FileDropzone from '@/Components/FileDropzone.vue';
import FolderImportButton from '@/Components/FolderImportButton.vue';
import ImportMarkdownGuide from '@/Components/ImportMarkdownGuide.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const dropzoneRef = ref(null);
const selectedFiles = ref([]);

const form = useForm({
    status: 'open',
    severity: 'medium',
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
    form.post(route('troubleshooting.import.store'), {
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
    <Head title="Import Troubleshooting" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('troubleshooting.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Troubleshooting</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Import Case (.md)</h1>
                </div>
                <FormHeaderActions
                    form-id="ts-import-form"
                    :cancel-href="route('troubleshooting.index')"
                    save-label="Import"
                    :processing="form.processing"
                    :save-disabled="!selectedFiles.length"
                />
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <p class="mb-6 text-[14px] text-[#6b7280]">
                Upload file markdown dengan section <code class="rounded bg-[#f5f5f5] px-1">##</code>.
                Setiap file menjadi satu troubleshooting case.
            </p>

            <form id="ts-import-form" @submit.prevent="submit" class="space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option value="open">Open</option>
                            <option value="investigating">Investigating</option>
                            <option value="solved">Solved</option>
                            <option value="workaround">Workaround</option>
                            <option value="failed">Failed</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Severity" />
                        <select v-model="form.severity" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
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

                <ImportMarkdownGuide type="troubleshooting" />
            </form>
        </div>
    </AuthenticatedLayout>
</template>
