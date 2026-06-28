<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import MarkdownEditor from '@/Components/MarkdownEditor.vue';
import HtmlContent from '@/Components/HtmlContent.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import RelatedItemsPicker from '@/Components/RelatedItemsPicker.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({ page: Object, categories: Array, books: Array, tags: Array, related: Array, linkable: Array });

const isHtmlOnly = computed(() => !!props.page.content_html && !props.page.content_markdown);

const tagInput = ref('');
const form = useForm({
    title: props.page.title,
    slug: props.page.slug,
    summary: props.page.summary ?? '',
    content_markdown: props.page.content_markdown ?? '',
    category_id: props.page.category_id,
    book_id: props.page.book_id,
    status: props.page.status,
    visibility: props.page.visibility,
    tag_names: props.page.tags?.map(t => t.name) ?? [],
    related: props.related?.map(r => ({ type: r.type, id: r.id })) ?? [],
});

const submit = () => {
    form.transform((data) => {
        if (isHtmlOnly.value && !data.content_markdown?.trim()) {
            const { content_markdown, ...rest } = data;
            return rest;
        }

        return data;
    }).put(route('wiki.update', props.page.slug));
};

const addTag = () => {
    const name = tagInput.value.trim();
    if (name && !form.tag_names.includes(name)) {
        form.tag_names.push(name);
        tagInput.value = '';
    }
};

const removeTag = (name) => {
    form.tag_names = form.tag_names.filter(t => t !== name);
};
</script>

<template>
    <Head :title="`Edit: ${page.title}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('wiki.show', page.slug)" class="text-[13px] text-[#6b7280] transition-colors hover:text-[#111111]">← Back</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Edit Page</h1>
                </div>
                <FormHeaderActions
                    form-id="wiki-edit-form"
                    :cancel-href="route('wiki.show', page.slug)"
                    save-label="Save Changes"
                    :processing="form.processing"
                />
            </div>
        </template>

        <form id="wiki-edit-form" @submit.prevent="submit" class="space-y-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Title" />
                        <input v-model="form.title" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111]" required />
                    </div>
                    <div>
                        <InputLabel value="Book (optional)" />
                        <select v-model="form.book_id" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option :value="null">Standalone — not in a book</option>
                            <option v-for="book in books" :key="book.id" :value="book.id">{{ book.title }}</option>
                        </select>
                        <p class="mt-1 text-[12px] text-[#898989]">Pindahkan halaman ke book di shelf, atau kosongkan untuk standalone.</p>
                    </div>
                </div>
                <div>
                    <InputLabel value="Summary" />
                    <input v-model="form.summary" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
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
                            <option value="draft">Draft</option><option value="review">Review</option><option value="tested">Tested</option><option value="production">Production</option><option value="deprecated">Deprecated</option><option value="archived">Archived</option>
                        </select>
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Visibility" />
                        <select v-model="form.visibility" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                            <option value="private">Private</option>
                            <option value="internal">Internal</option>
                            <option value="public">Public</option>
                        </select>
                    </div>
                </div>
                <div>
                    <InputLabel value="Tags" />
                    <div class="mt-1.5 flex gap-2">
                        <input v-model="tagInput" @keydown.enter.prevent="addTag" placeholder="Add tag..." class="flex-1 rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                        <button type="button" @click="addTag" class="rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[13px]">Add</button>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        <span v-for="tag in form.tag_names" :key="tag" class="inline-flex items-center gap-1 rounded-[9999px] bg-[#f5f5f5] px-2.5 py-0.5 text-[12px]">
                            {{ tag }}
                            <button type="button" @click="removeTag(tag)">×</button>
                        </span>
                    </div>
                </div>
                <RelatedItemsPicker v-model="form.related" :linkable="linkable" />
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div v-if="isHtmlOnly" class="space-y-4">
                    <p class="text-[13px] text-[#6b7280]">
                        Halaman ini diimport dari HTML. Konten HTML dipertahankan saat Anda mengubah metadata seperti status.
                        Tulis markdown di bawah jika ingin mengonversi halaman ke format markdown.
                    </p>
                    <div class="rounded-[8px] border border-[#e5e7eb] bg-[#f8f9fa] p-4">
                        <HtmlContent :html="page.content_html" />
                    </div>
                    <div>
                        <InputLabel value="Convert to Markdown (optional)" />
                        <MarkdownEditor v-model="form.content_markdown" :rows="12" placeholder="Leave empty to keep HTML content..." />
                    </div>
                </div>
                <MarkdownEditor v-else v-model="form.content_markdown" :rows="24" />
            </div>
        </form>
    </AuthenticatedLayout>
</template>
