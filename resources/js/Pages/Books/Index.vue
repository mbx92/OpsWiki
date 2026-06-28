<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ books: Array });
</script>

<template>
    <Head title="Bookshelf" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Bookshelf</h1>
                <Link :href="route('wiki.import')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">
                    Import MD/HTML
                </Link>
            </div>
        </template>

        <EmptyState v-if="!books.length" title="Shelf is empty" description="Import a folder of markdown or HTML files to create your first book.">
            <Link :href="route('wiki.import')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[14px] font-[600] text-white">Import files</Link>
        </EmptyState>

        <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Link
                v-for="book in books"
                :key="book.id"
                :href="route('books.show', book.slug)"
                class="group rounded-[12px] border border-[#e5e7eb] bg-white p-6 hover:bg-[#f8f9fa]"
            >
                <div class="mb-4 flex h-12 w-10 items-end justify-center rounded-r-[4px] rounded-l-[2px] bg-[#111111] shadow-sm">
                    <div class="mb-2 h-8 w-8 rounded-[2px] bg-[#374151]"></div>
                </div>
                <h2 class="text-[16px] font-[600] text-[#111111] group-hover:underline">{{ book.title }}</h2>
                <p v-if="book.description" class="mt-1 line-clamp-2 text-[13px] text-[#6b7280]">{{ book.description }}</p>
                <div class="mt-3 flex items-center gap-3 text-[12px] text-[#898989]">
                    <span>{{ book.pages_count }} pages</span>
                    <span v-if="book.category">{{ book.category.name }}</span>
                </div>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
