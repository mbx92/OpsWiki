<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import FileDropzone from '@/Components/FileDropzone.vue';
import FolderImportButton from '@/Components/FolderImportButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ categories: Array, book: Object });

const appendToBook = computed(() => !!props.book);
const cancelHref = computed(() => (
    appendToBook.value ? route('books.show', props.book.slug) : route('wiki.index')
));

const dropzoneRef = ref(null);
const selectedFiles = ref([]);

const form = useForm({
    book_id: props.book?.id ?? null,
    book_title: props.book?.title ?? '',
    book_description: props.book?.description ?? '',
    category_id: props.book?.category_id ?? null,
    status: 'draft',
    files: [],
});

const applyFiles = (files) => {
    const list = Array.isArray(files) ? files : (files ? [files] : []);
    selectedFiles.value = list.filter((file) => /\.(md|markdown|html|htm|zip)$/i.test(file.name));
    form.files = selectedFiles.value;
};

const onFolderSelected = (files) => {
    applyFiles(files.filter((file) => /\.(md|markdown|html|htm)$/i.test(file.name)));
    dropzoneRef.value?.clear();
};

const submit = () => {
    form.post(route('wiki.import.store'), {
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
    <Head title="Import Pages" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="cancelHref" class="text-[13px] text-[#6b7280] transition-colors hover:text-[#111111]">
                        ← {{ appendToBook ? book.title : 'Wiki' }}
                    </Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">
                        {{ appendToBook ? 'Import to Book' : 'Import MD / HTML' }}
                    </h1>
                </div>
                <FormHeaderActions
                    form-id="wiki-import-form"
                    :cancel-href="cancelHref"
                    :save-label="appendToBook ? 'Add pages' : 'Import to Shelf'"
                    :processing="form.processing"
                    :save-disabled="!selectedFiles.length"
                />
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
            <p class="mb-6 text-[14px] text-[#6b7280]">
                <template v-if="appendToBook">
                    Tambahkan halaman baru ke book <strong>{{ book.title }}</strong>. File akan ditambahkan setelah halaman yang sudah ada.
                </template>
                <template v-else>
                    Upload markdown atau HTML files untuk dijadikan wiki pages dalam satu book di shelf.
                    Bisa upload banyak file sekaligus, atau satu file <code class="rounded bg-[#f5f5f5] px-1">.zip</code> berisi folder docs.
                </template>
            </p>

            <form id="wiki-import-form" @submit.prevent="submit" class="space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <input v-if="appendToBook" type="hidden" v-model="form.book_id" />

                <div v-if="appendToBook">
                    <InputLabel value="Book" />
                    <input
                        :value="book.title"
                        disabled
                        class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-[#f8f9fa] px-3 py-2 text-[14px] text-[#374151]"
                    />
                </div>
                <div v-else>
                    <InputLabel value="Book title (shelf name)" />
                    <input v-model="form.book_title" required placeholder="e.g. Proxmox Guide" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]" />
                    <p v-if="form.errors.book_title" class="mt-1 text-[13px] text-[#ef4444]">{{ form.errors.book_title }}</p>
                </div>

                <div>
                    <InputLabel value="Description (optional)" />
                    <textarea v-model="form.book_description" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
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
                        <InputLabel value="Page status" />
                        <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option value="draft">Draft</option>
                            <option value="review">Review</option>
                            <option value="tested">Tested</option>
                            <option value="production">Production</option>
                        </select>
                    </div>
                </div>

                <div>
                    <InputLabel value="Files (.md, .html, or .zip)" />
                    <FileDropzone
                        ref="dropzoneRef"
                        multiple
                        accept=".md,.markdown,.html,.htm,.zip"
                        :max-filesize="20"
                        hint="Tarik file markdown, HTML, atau zip ke sini"
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
                    <p class="mt-1 text-[12px] text-[#898989]">Semua file .md/.html di dalam folder akan diimport berurutan.</p>
                </div>

                <ul v-if="selectedFiles.length && selectedFiles[0]?.webkitRelativePath" class="space-y-1">
                    <li v-for="file in selectedFiles" :key="file.webkitRelativePath + file.size" class="text-[13px] text-[#6b7280]">
                        {{ file.webkitRelativePath }} ({{ (file.size / 1024).toFixed(1) }} KB)
                    </li>
                </ul>

                <div class="rounded-[8px] bg-[#f8f9fa] p-4 text-[13px] text-[#6b7280]">
                    <p class="font-[600] text-[#374151]">Cara kerja:</p>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        <li><strong>.md</strong> — judul dari <code># Heading</code> atau nama file</li>
                        <li><strong>.html</strong> — judul dari <code>&lt;title&gt;</code> atau <code>&lt;h1&gt;</code></li>
                        <li><strong>.zip</strong> — semua .md/.html di dalam zip diimport berurutan</li>
                        <li><strong>Folder</strong> — pilih folder lokal, semua .md/.html di dalamnya diimport</li>
                        <li>Setiap file jadi satu wiki page dalam book yang sama</li>
                    </ul>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
