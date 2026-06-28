<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tenant: Object,
    books: Array,
    pages: Array,
    filters: Object,
    stats: Object,
});

const q = ref(props.filters?.q ?? '');

const search = () => {
    router.get(route('portal.index'), { q: q.value || undefined }, { preserveState: true });
};

const isEmpty = () => !props.books.length && !props.pages.length;
</script>

<template>
    <Head :title="`${tenant.name} — Docs`" />
    <PublicLayout>
        <div class="mx-auto max-w-4xl">
            <div class="mb-8">
                <p class="text-[13px] font-[500] uppercase tracking-wider text-[#898989]">Public documentation</p>
                <h1 class="mt-1 text-[32px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ tenant.name }}</h1>
                <p class="mt-2 text-[15px] text-[#6b7280]">
                    {{ stats.books }} book<span v-if="stats.books !== 1">s</span>
                    · {{ stats.pages }} standalone page<span v-if="stats.pages !== 1">s</span>
                </p>
            </div>

            <form @submit.prevent="search" class="mb-8">
                <input
                    v-model="q"
                    type="search"
                    placeholder="Search public docs..."
                    class="w-full rounded-[8px] border border-[#e5e7eb] bg-white px-4 py-2.5 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]"
                />
            </form>

            <div v-if="isEmpty()" class="rounded-[12px] border border-dashed border-[#e5e7eb] bg-white px-6 py-12 text-center">
                <p class="text-[15px] font-[600] text-[#111111]">No public documentation yet</p>
                <p class="mt-2 text-[14px] text-[#6b7280]">Set wiki pages or books to <strong>Public</strong> visibility to show them here.</p>
            </div>

            <div v-else class="space-y-10">
                <section v-if="books.length">
                    <h2 class="mb-4 text-[13px] font-[600] uppercase tracking-wider text-[#898989]">Books</h2>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <Link
                            v-for="book in books"
                            :key="book.id"
                            :href="route('share.books.show', book.slug)"
                            class="rounded-[12px] border border-[#e5e7eb] bg-white p-5 hover:bg-[#f8f9fa]"
                        >
                            <p class="text-[16px] font-[600] text-[#111111]">{{ book.title }}</p>
                            <p v-if="book.description" class="mt-1 line-clamp-2 text-[13px] text-[#6b7280]">{{ book.description }}</p>
                            <p class="mt-3 text-[12px] text-[#898989]">{{ book.pages?.length ?? 0 }} pages</p>
                        </Link>
                    </div>
                </section>

                <section v-if="pages.length">
                    <h2 class="mb-4 text-[13px] font-[600] uppercase tracking-wider text-[#898989]">Pages</h2>
                    <div class="space-y-2">
                        <Link
                            v-for="page in pages"
                            :key="page.id"
                            :href="route('share.pages.show', page.slug)"
                            class="block rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]"
                        >
                            <p class="text-[15px] font-[600] text-[#111111]">{{ page.title }}</p>
                            <p v-if="page.summary" class="mt-1 text-[13px] text-[#6b7280]">{{ page.summary }}</p>
                        </Link>
                    </div>
                </section>
            </div>
        </div>
    </PublicLayout>
</template>
