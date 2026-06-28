<script setup>
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({ book: Object });
</script>

<template>
    <Head :title="book.title" />
    <PublicLayout>
        <div class="grid gap-6 lg:grid-cols-4">
            <aside class="self-start lg:sticky lg:top-24 lg:col-span-1 lg:max-h-[calc(100vh-6rem)] lg:overflow-y-auto">
                <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-4">
                    <h2 class="mb-3 text-[13px] font-[600] uppercase tracking-wider text-[#898989]">Contents</h2>
                    <nav class="space-y-0.5">
                        <Link
                            v-for="(page, index) in book.pages"
                            :key="page.id"
                            :href="route('share.pages.show', page.slug)"
                            class="flex items-start gap-2 rounded-[6px] px-2 py-1.5 text-[13px] text-[#374151] hover:bg-[#f8f9fa] hover:text-[#111111]"
                        >
                            <span class="mt-0.5 shrink-0 text-[#898989]">{{ index + 1 }}.</span>
                            <span>{{ page.title }}</span>
                        </Link>
                    </nav>
                    <p v-if="!book.pages?.length" class="text-[13px] text-[#898989]">No pages in this book.</p>
                </div>
            </aside>

            <div class="space-y-4 lg:col-span-3">
                <div>
                    <Link :href="route('portal.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Docs</Link>
                    <p class="mt-2 text-[13px] font-[500] uppercase tracking-wider text-[#898989]">Public book</p>
                    <h1 class="mt-1 text-[28px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ book.title }}</h1>
                    <p v-if="book.description" class="mt-3 text-[15px] text-[#6b7280]">{{ book.description }}</p>
                </div>

                <Link
                    v-for="(page, index) in book.pages"
                    :key="page.id"
                    :href="route('share.pages.show', page.slug)"
                    class="flex items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]"
                >
                    <div class="flex items-center gap-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-[8px] bg-[#f5f5f5] text-[13px] font-[600] text-[#898989]">{{ index + 1 }}</span>
                        <div>
                            <div class="text-[15px] font-[600] text-[#111111]">{{ page.title }}</div>
                            <p v-if="page.summary" class="mt-0.5 text-[13px] text-[#6b7280]">{{ page.summary }}</p>
                        </div>
                    </div>
                </Link>
            </div>
        </div>
    </PublicLayout>
</template>
