<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import ShareDialog from '@/Components/ShareDialog.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ book: Object, publicUrl: String, availablePages: Array });

const shareOpen = ref(false);
const selectedPageIds = ref([]);

const attachForm = useForm({
    page_ids: [],
});

const destroy = async () => {
    if (await confirmDelete('Pages will remain in wiki.', 'Remove this book from shelf?')) {
        router.delete(route('books.destroy', props.book.slug));
    }
};

const togglePage = (id) => {
    if (selectedPageIds.value.includes(id)) {
        selectedPageIds.value = selectedPageIds.value.filter((x) => x !== id);
    } else {
        selectedPageIds.value.push(id);
    }
};

const attachPages = () => {
    attachForm.page_ids = selectedPageIds.value;
    attachForm.post(route('books.pages.attach', props.book.slug), {
        preserveScroll: true,
        onSuccess: () => {
            selectedPageIds.value = [];
        },
    });
};

const movePage = (page, direction) => {
    router.post(route('books.pages.move', { book: props.book.slug, page: page.id }), { direction }, { preserveScroll: true });
};
</script>

<template>
    <Head :title="book.title" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('books.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Shelf</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ book.title }}</h1>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button
                        type="button"
                        @click="shareOpen = true"
                        class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]"
                    >
                        Share
                    </button>
                    <Link
                        :href="route('wiki.import', { book: book.slug })"
                        class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]"
                    >
                        Import pages
                    </Link>
                    <a
                        :href="route('books.export.static', book.slug)"
                        class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]"
                    >
                        Export static site
                    </a>
                    <a
                        :href="route('books.export', book.slug)"
                        class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]"
                    >
                        Export markdown
                    </a>
                    <button @click="destroy" class="rounded-[8px] border border-[#fecaca] px-3 py-1.5 text-[13px] font-[500] text-[#ef4444] transition-colors hover:bg-[#fef2f2]">Remove book</button>
                </div>
            </div>
        </template>

        <ShareDialog
            :show="shareOpen"
            title="Share book"
            description="Anyone with the link can read this book without signing in."
            :visibility="book.visibility ?? 'private'"
            :public-url="publicUrl"
            :share-path="route('share.books.show', book.slug)"
            :update-route="route('books.sharing', book.slug)"
            show-make-pages-public
            @close="shareOpen = false"
        />

        <div class="grid gap-6 lg:grid-cols-4">
            <aside class="self-start lg:sticky lg:top-[9rem] lg:col-span-1 lg:max-h-[calc(100vh-9rem)] lg:overflow-y-auto">
                <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-4">
                    <h2 class="mb-3 text-[13px] font-[600] uppercase tracking-wider text-[#898989]">Contents</h2>
                    <nav class="space-y-0.5">
                        <div
                            v-for="(page, index) in book.pages"
                            :key="page.id"
                            class="group flex items-center gap-1 rounded-[6px] px-1 py-0.5 hover:bg-[#f8f9fa]"
                        >
                            <Link
                                :href="route('wiki.show', page.slug)"
                                class="flex min-w-0 flex-1 items-start gap-2 px-1 py-1 text-[13px] text-[#374151] hover:text-[#111111]"
                            >
                                <span class="mt-0.5 shrink-0 text-[#898989]">{{ index + 1 }}.</span>
                                <span class="truncate">{{ page.title }}</span>
                            </Link>
                            <div class="flex shrink-0 opacity-0 transition-opacity group-hover:opacity-100">
                                <button
                                    type="button"
                                    class="rounded px-1 text-[11px] text-[#898989] hover:bg-white hover:text-[#111111] disabled:opacity-30"
                                    :disabled="index === 0"
                                    title="Move up"
                                    @click.stop="movePage(page, 'up')"
                                >↑</button>
                                <button
                                    type="button"
                                    class="rounded px-1 text-[11px] text-[#898989] hover:bg-white hover:text-[#111111] disabled:opacity-30"
                                    :disabled="index === book.pages.length - 1"
                                    title="Move down"
                                    @click.stop="movePage(page, 'down')"
                                >↓</button>
                            </div>
                        </div>
                    </nav>
                    <p v-if="!book.pages?.length" class="text-[13px] text-[#898989]">No pages in this book.</p>
                </div>
            </aside>

            <div class="space-y-3 lg:col-span-3">
                <p v-if="book.description" class="text-[15px] text-[#6b7280]">{{ book.description }}</p>

                <div v-if="availablePages.length" class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                    <h2 class="text-[14px] font-[600] text-[#111111]">Add existing wiki pages</h2>
                    <p class="mt-1 text-[13px] text-[#6b7280]">Halaman standalone yang sudah ada bisa ditambahkan ke book ini.</p>
                    <div class="mt-3 max-h-48 space-y-1 overflow-y-auto rounded-[8px] border border-[#e5e7eb] p-3">
                        <label
                            v-for="page in availablePages"
                            :key="page.id"
                            class="flex cursor-pointer items-center justify-between gap-3 rounded-[6px] px-2 py-1.5 text-[14px] hover:bg-[#f8f9fa]"
                        >
                            <span class="flex items-center gap-2">
                                <input type="checkbox" :checked="selectedPageIds.includes(page.id)" @change="togglePage(page.id)" />
                                {{ page.title }}
                            </span>
                            <StatusBadge :status="page.status" />
                        </label>
                    </div>
                    <button
                        type="button"
                        class="mt-3 rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50"
                        :disabled="!selectedPageIds.length || attachForm.processing"
                        @click="attachPages"
                    >
                        Add to book
                    </button>
                    <p v-if="attachForm.errors.page_ids" class="mt-2 text-[13px] text-[#ef4444]">{{ attachForm.errors.page_ids }}</p>
                </div>

                <Link
                    v-for="(page, index) in book.pages"
                    :key="page.id"
                    :href="route('wiki.show', page.slug)"
                    class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]"
                >
                    <div class="flex items-center gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[8px] bg-[#f5f5f5] text-[13px] font-[600] text-[#898989]">{{ index + 1 }}</span>
                        <div>
                            <div class="text-[15px] font-[600] text-[#111111]">{{ page.title }}</div>
                            <p v-if="page.summary" class="mt-0.5 text-[13px] text-[#6b7280]">{{ page.summary }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-[6px] border border-[#e5e7eb] px-2 py-1 text-[12px] text-[#374151] hover:bg-[#f8f9fa] disabled:opacity-30"
                            :disabled="index === 0"
                            @click.prevent="movePage(page, 'up')"
                        >↑</button>
                        <button
                            type="button"
                            class="rounded-[6px] border border-[#e5e7eb] px-2 py-1 text-[12px] text-[#374151] hover:bg-[#f8f9fa] disabled:opacity-30"
                            :disabled="index === book.pages.length - 1"
                            @click.prevent="movePage(page, 'down')"
                        >↓</button>
                        <StatusBadge :status="page.status" />
                    </div>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
