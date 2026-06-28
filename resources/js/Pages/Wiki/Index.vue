<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TagBadge from '@/Components/TagBadge.vue';
import EmptyState from '@/Components/EmptyState.vue';
import { usePlanFeatures } from '@/Composables/usePlanFeatures';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ pages: Object, categories: Array, filters: Object });

const { hasFeature } = usePlanFeatures();

const q = ref(props.filters?.q ?? '');
const scope = ref(props.filters?.scope ?? '');

const scopes = [
    { value: '', label: 'All' },
    { value: 'standalone', label: 'Standalone' },
    { value: 'in_book', label: 'In book' },
];

const search = () => router.get(route('wiki.index'), { q: q.value, scope: scope.value || undefined }, { preserveState: true });

const setScope = (value) => {
    scope.value = value;
    search();
};
</script>

<template>
    <Head title="Wiki" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Wiki</h1>
                <div class="flex gap-2">
                    <a v-if="hasFeature('wiki.export')" :href="route('wiki.export.static')" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">Export static</a>
                    <Link v-if="hasFeature('wiki.import')" :href="route('wiki.import')" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">Import</Link>
                    <Link :href="route('wiki.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">+ New Page</Link>
                </div>
            </div>
        </template>

        <div class="mb-4 flex flex-wrap items-center gap-3">
            <form @submit.prevent="search" class="flex-1 min-w-[200px]">
                <input v-model="q" placeholder="Search pages..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]" />
            </form>
            <div class="flex items-center gap-2">
                <button
                    v-for="option in scopes"
                    :key="option.value"
                    type="button"
                    class="rounded-[8px] border px-3 py-2 text-[13px] font-[500] transition-colors"
                    :class="scope === option.value
                        ? 'border-[#111111] bg-[#111111] text-white'
                        : 'border-[#e5e7eb] text-[#374151] hover:bg-[#f8f9fa]'"
                    @click="setScope(option.value)"
                >
                    {{ option.label }}
                </button>
            </div>
        </div>

        <EmptyState v-if="!pages.data.length" title="No wiki pages" description="Start documenting your infrastructure and workflows.">
            <Link :href="route('wiki.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[14px] font-[600] text-white">Create first page</Link>
        </EmptyState>

        <div v-else class="space-y-2">
            <Link
                v-for="page in pages.data"
                :key="page.id"
                :href="route('wiki.show', page.slug)"
                class="block rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-[15px] font-[600] text-[#111111]">{{ page.title }}</span>
                            <span v-if="page.book" class="rounded-[9999px] bg-[#eff6ff] px-2 py-0.5 text-[11px] font-[500] text-[#2563eb]">
                                {{ page.book.title }}
                            </span>
                        </div>
                        <p v-if="page.summary" class="mt-1 text-[13px] text-[#6b7280]">{{ page.summary }}</p>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <TagBadge v-for="tag in page.tags" :key="tag.id" :name="tag.name" />
                        </div>
                    </div>
                    <StatusBadge :status="page.status" />
                </div>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
