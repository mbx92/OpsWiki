<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CopyButton from '@/Components/CopyButton.vue';
import EmptyState from '@/Components/EmptyState.vue';
import PlatformBadge from '@/Components/PlatformBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ snippets: Object, categories: Array, filters: Object, platforms: Array });

const q = ref(props.filters.q ?? '');

const search = () => router.get(route('snippets.index'), { q: q.value, favorite: props.filters.favorite }, { preserveState: true });

const toggleFavorite = (snippet) => {
    router.post(route('snippets.favorite', snippet.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Snippets" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Snippets</h1>
                <Link :href="route('snippets.create')" data-page-tour="page-actions" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">+ New Snippet</Link>
            </div>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-2">
            <form @submit.prevent="search" class="flex-1 min-w-[200px]">
                <input v-model="q" placeholder="Search commands..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]" />
            </form>
            <Link
                :href="route('snippets.index', { favorite: filters.favorite ? undefined : 1, q: filters.q })"
                class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500]"
                :class="filters.favorite ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'"
            >★ Favorites</Link>
        </div>

        <EmptyState v-if="!snippets.data.length" title="No snippets yet" description="Save commands you use frequently so you never lose them.">
            <Link :href="route('snippets.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[14px] font-[600] text-white">Create snippet</Link>
        </EmptyState>

        <div v-else class="space-y-3">
            <div v-for="snippet in snippets.data" :key="snippet.id" class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="mb-2 flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                @click="toggleFavorite(snippet)"
                                class="text-[16px] leading-none"
                                :class="snippet.is_favorite ? 'text-[#f59e0b]' : 'text-[#d1d5db] hover:text-[#f59e0b]'"
                                :title="snippet.is_favorite ? 'Remove from favorites' : 'Add to favorites'"
                            >★</button>
                            <Link :href="route('snippets.edit', snippet.id)" class="text-[15px] font-[600] text-[#111111] hover:underline">{{ snippet.title }}</Link>
                            <PlatformBadge v-if="snippet.platform" :platform="snippet.platform" />
                        </div>
                        <p v-if="snippet.description" class="mt-1.5 text-[13px] text-[#6b7280]">{{ snippet.description }}</p>
                    </div>
                    <CopyButton :text="snippet.command" />
                </div>
                <pre class="overflow-x-auto rounded-[8px] bg-[#101010] p-4 text-[13px] leading-relaxed text-[#e5e7eb]"><code>{{ snippet.command }}</code></pre>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
