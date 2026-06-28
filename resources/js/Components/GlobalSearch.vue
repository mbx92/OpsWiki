<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const query = ref('');
const results = ref(null);
const loading = ref(false);
const open = ref(false);
const root = ref(null);

let debounceTimer = null;
let abortController = null;

const hasResults = () => {
    if (!results.value) return false;
    const r = results.value;
    return r.pages?.length || r.snippets?.length || r.inbox?.length
        || r.sops?.length || r.troubleshooting?.length || r.projects?.length;
};

const fetchResults = async (q) => {
    if (q.length < 2) {
        results.value = null;
        loading.value = false;
        return;
    }

    abortController?.abort();
    abortController = new AbortController();
    loading.value = true;

    try {
        const { data } = await axios.get(route('search.query'), {
            params: { q, smart: 1 },
            signal: abortController.signal,
        });
        results.value = data;
    } catch (e) {
        if (e.code !== 'ERR_CANCELED') {
            results.value = null;
        }
    } finally {
        loading.value = false;
    }
};

watch(query, (value) => {
    const q = value.trim();
    open.value = q.length >= 2;

    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => fetchResults(q), 250);
});

const onFocus = () => {
    if (query.value.trim().length >= 2) {
        open.value = true;
    }
};

const close = () => {
    open.value = false;
};

const goTo = (href) => {
    close();
    router.visit(href);
};

const viewAll = () => {
    const q = query.value.trim();
    if (q.length >= 2) {
        close();
        router.get(route('search.index'), { q });
    }
};

const onClickOutside = (event) => {
    if (root.value && !root.value.contains(event.target)) {
        close();
    }
};

onMounted(() => document.addEventListener('click', onClickOutside));
onUnmounted(() => {
    document.removeEventListener('click', onClickOutside);
    clearTimeout(debounceTimer);
    abortController?.abort();
});
</script>

<template>
    <div ref="root" class="relative flex-1 max-w-md">
        <form @submit.prevent="viewAll" class="relative">
            <svg class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#898989]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                v-model="query"
                type="search"
                placeholder="Search wiki, snippets, SOPs..."
                autocomplete="off"
                class="h-9 w-full rounded-[8px] border border-[#e5e7eb] bg-white pl-9 pr-3 text-[14px] text-[#111111] outline-none placeholder:text-[#898989] focus:border-[#111111]"
                @focus="onFocus"
            />
        </form>

        <div
            v-if="open && query.trim().length >= 2"
            class="absolute left-0 right-0 top-full z-50 mt-1 max-h-[70vh] overflow-y-auto rounded-[12px] border border-[#e5e7eb] bg-white py-2 shadow-lg"
        >
            <div v-if="loading" class="px-4 py-3 text-[13px] text-[#898989]">Searching...</div>

            <template v-else-if="hasResults()">
                <section v-if="results.pages?.length" class="px-2 py-1">
                    <p class="px-2 py-1 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">Wiki</p>
                    <button
                        v-for="page in results.pages"
                        :key="`page-${page.id}`"
                        type="button"
                        class="block w-full rounded-[6px] px-2 py-2 text-left hover:bg-[#f8f9fa]"
                        @click="goTo(route('wiki.show', page.slug))"
                    >
                        <div class="text-[13px] font-[500] text-[#111111]">{{ page.title }}</div>
                        <p v-if="page.summary" class="truncate text-[12px] text-[#898989]">{{ page.summary }}</p>
                    </button>
                </section>

                <section v-if="results.sops?.length" class="px-2 py-1">
                    <p class="px-2 py-1 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">SOPs</p>
                    <button
                        v-for="sop in results.sops"
                        :key="`sop-${sop.id}`"
                        type="button"
                        class="block w-full rounded-[6px] px-2 py-2 text-left text-[13px] font-[500] text-[#111111] hover:bg-[#f8f9fa]"
                        @click="goTo(route('sops.show', sop.slug))"
                    >
                        {{ sop.title }}
                    </button>
                </section>

                <section v-if="results.troubleshooting?.length" class="px-2 py-1">
                    <p class="px-2 py-1 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">Troubleshooting</p>
                    <button
                        v-for="item in results.troubleshooting"
                        :key="`ts-${item.id}`"
                        type="button"
                        class="block w-full rounded-[6px] px-2 py-2 text-left text-[13px] font-[500] text-[#111111] hover:bg-[#f8f9fa]"
                        @click="goTo(route('troubleshooting.show', item.slug))"
                    >
                        {{ item.title }}
                    </button>
                </section>

                <section v-if="results.projects?.length" class="px-2 py-1">
                    <p class="px-2 py-1 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">Projects</p>
                    <button
                        v-for="project in results.projects"
                        :key="`project-${project.id}`"
                        type="button"
                        class="block w-full rounded-[6px] px-2 py-2 text-left text-[13px] font-[500] text-[#111111] hover:bg-[#f8f9fa]"
                        @click="goTo(route('projects.show', project.slug))"
                    >
                        {{ project.name }}
                    </button>
                </section>

                <section v-if="results.snippets?.length" class="px-2 py-1">
                    <p class="px-2 py-1 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">Snippets</p>
                    <button
                        v-for="snippet in results.snippets"
                        :key="`snippet-${snippet.id}`"
                        type="button"
                        class="block w-full rounded-[6px] px-2 py-2 text-left hover:bg-[#f8f9fa]"
                        @click="goTo(route('snippets.edit', snippet.id))"
                    >
                        <div class="text-[13px] font-[500] text-[#111111]">{{ snippet.title }}</div>
                        <p class="truncate text-[12px] text-[#898989]">{{ snippet.command }}</p>
                    </button>
                </section>

                <section v-if="results.inbox?.length" class="px-2 py-1">
                    <p class="px-2 py-1 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">Inbox</p>
                    <button
                        v-for="item in results.inbox"
                        :key="`inbox-${item.id}`"
                        type="button"
                        class="block w-full rounded-[6px] px-2 py-2 text-left text-[13px] font-[500] text-[#111111] hover:bg-[#f8f9fa]"
                        @click="goTo(route('inbox.show', item.id))"
                    >
                        {{ item.title }}
                    </button>
                </section>

                <div class="border-t border-[#e5e7eb] px-2 pt-1">
                    <button type="button" class="w-full rounded-[6px] px-2 py-2 text-left text-[13px] font-[500] text-[#6b7280] hover:bg-[#f8f9fa]" @click="viewAll">
                        View all results →
                    </button>
                </div>
            </template>

            <p v-else class="px-4 py-3 text-[13px] text-[#898989]">No results for "{{ query.trim() }}"</p>
        </div>
    </div>
</template>
