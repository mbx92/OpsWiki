<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    entries: { type: Array, default: () => [] },
    orphans: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({ total: 0, connected: 0, orphans: 0, relations: 0 }) },
    types: { type: Array, default: () => [] },
});

const q = ref('');
const typeFilter = ref('all');
const showOrphans = ref(false);

const typeLabels = {
    pages: 'Wiki',
    sops: 'SOP',
    troubleshooting_cases: 'Troubleshooting',
    projects: 'Project',
    snippets: 'Snippet',
};

const typeColors = {
    pages: 'bg-[#111111] text-white',
    sops: 'bg-[#dbeafe] text-[#1e40af]',
    troubleshooting_cases: 'bg-[#fee2e2] text-[#991b1b]',
    projects: 'bg-[#d1fae5] text-[#065f46]',
    snippets: 'bg-[#ede9fe] text-[#5b21b6]',
};

const filterOptions = computed(() => [
    { value: 'all', label: 'Semua' },
    ...props.types.map((t) => ({ value: t, label: typeLabels[t] ?? t.replace('_', ' ') })),
]);

const matchesQuery = (label, related = []) => {
    const term = q.value.trim().toLowerCase();
    if (!term) return true;

    if (label.toLowerCase().includes(term)) return true;

    return related.some((item) => item.label.toLowerCase().includes(term));
};

const filteredEntries = computed(() =>
    props.entries.filter((entry) => {
        if (typeFilter.value !== 'all' && entry.type !== typeFilter.value) return false;
        return matchesQuery(entry.label, entry.related);
    }),
);

const filteredOrphans = computed(() =>
    props.orphans.filter((item) => {
        if (typeFilter.value !== 'all' && item.type !== typeFilter.value) return false;
        return matchesQuery(item.label);
    }),
);

const hasData = computed(() => props.stats.total > 0);
const hasConnections = computed(() => props.stats.connected > 0);
</script>

<template>
    <Head title="Knowledge Graph" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Knowledge Graph</h1>
        </template>

        <p class="mb-4 text-[14px] text-[#6b7280]">
            Peta relasi antar dokumentasi. Hanya item yang saling terhubung ditampilkan utama — bukan diagram, melainkan daftar yang bisa dicari.
        </p>

        <div v-if="hasData" class="mb-4 flex flex-wrap items-center gap-2">
            <input
                v-model="q"
                type="search"
                placeholder="Cari judul atau relasi..."
                class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]"
            />
            <button
                v-for="opt in filterOptions"
                :key="opt.value"
                type="button"
                class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500] capitalize"
                :class="typeFilter === opt.value ? 'bg-[#111111] text-white' : 'border border-[#e5e7eb] bg-white text-[#374151]'"
                @click="typeFilter = opt.value"
            >
                {{ opt.label }}
            </button>
        </div>

        <div v-if="hasData" class="mb-6 flex flex-wrap gap-4 text-[13px] text-[#6b7280]">
            <span><strong class="text-[#111111]">{{ stats.connected }}</strong> terhubung</span>
            <span><strong class="text-[#111111]">{{ stats.relations }}</strong> relasi</span>
            <span v-if="stats.orphans"><strong class="text-[#111111]">{{ stats.orphans }}</strong> tanpa relasi</span>
        </div>

        <div v-if="!hasData" class="rounded-[12px] border border-[#e5e7eb] bg-white p-8 text-center text-[14px] text-[#898989]">
            Belum ada konten. Buat wiki page lalu hubungkan lewat <strong>Related pages</strong> di form edit.
        </div>

        <div v-else-if="!hasConnections" class="rounded-[12px] border border-[#e5e7eb] bg-white p-8 text-center text-[14px] text-[#898989]">
            Ada {{ stats.total }} item, tapi belum ada relasi. Edit wiki page dan centang <strong>Related pages</strong> untuk mulai membangun graph.
        </div>

        <div v-else class="space-y-3">
            <article
                v-for="entry in filteredEntries"
                :key="entry.id"
                class="rounded-[12px] border border-[#e5e7eb] bg-white p-5"
            >
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex shrink-0 items-center rounded-[6px] px-2 py-0.5 text-[11px] font-[600] uppercase tracking-wide"
                                :class="typeColors[entry.type] ?? 'bg-[#f5f5f5] text-[#374151]'"
                            >
                                {{ typeLabels[entry.type] ?? entry.type }}
                            </span>
                            <Link
                                v-if="entry.url"
                                :href="entry.url"
                                class="text-[15px] font-[600] text-[#111111] hover:underline"
                            >
                                {{ entry.label }}
                            </Link>
                            <span v-else class="text-[15px] font-[600] text-[#111111]">{{ entry.label }}</span>
                        </div>
                        <p class="mt-1 text-[13px] text-[#898989]">
                            {{ entry.relation_count }} relasi
                        </p>
                    </div>
                </div>

                <div v-if="entry.related.length" class="mt-4 border-t border-[#f3f4f6] pt-4">
                    <p class="mb-2 text-[11px] font-[600] uppercase tracking-wider text-[#898989]">Terhubung ke</p>
                    <div class="flex flex-wrap gap-2">
                        <Link
                            v-for="rel in entry.related"
                            :key="rel.id"
                            :href="rel.url"
                            class="inline-flex max-w-full items-center gap-1.5 rounded-[8px] border border-[#e5e7eb] bg-[#f8f9fa] px-2.5 py-1.5 text-[13px] text-[#374151] hover:border-[#111111] hover:bg-white"
                        >
                            <span
                                class="h-1.5 w-1.5 shrink-0 rounded-full"
                                :class="{
                                    'bg-[#111111]': rel.type === 'pages',
                                    'bg-[#2563eb]': rel.type === 'sops',
                                    'bg-[#dc2626]': rel.type === 'troubleshooting_cases',
                                    'bg-[#059669]': rel.type === 'projects',
                                    'bg-[#7c3aed]': rel.type === 'snippets',
                                }"
                            />
                            <span class="truncate">{{ rel.label }}</span>
                        </Link>
                    </div>
                </div>
            </article>

            <p v-if="!filteredEntries.length" class="py-8 text-center text-[14px] text-[#898989]">
                Tidak ada hasil untuk filter ini.
            </p>
        </div>

        <div v-if="filteredOrphans.length" class="mt-8">
            <button
                type="button"
                class="flex w-full items-center justify-between rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-3 text-left text-[14px] font-[500] text-[#374151] hover:bg-[#f8f9fa]"
                @click="showOrphans = !showOrphans"
            >
                <span>Item tanpa relasi ({{ filteredOrphans.length }})</span>
                <span class="text-[#898989]">{{ showOrphans ? '▲' : '▼' }}</span>
            </button>

            <div v-if="showOrphans" class="mt-2 space-y-1 rounded-[12px] border border-[#e5e7eb] bg-white p-3">
                <Link
                    v-for="item in filteredOrphans"
                    :key="item.id"
                    :href="item.url"
                    class="flex items-center justify-between rounded-[8px] px-3 py-2 text-[14px] hover:bg-[#f8f9fa]"
                >
                    <span class="font-[500] text-[#111111]">{{ item.label }}</span>
                    <span class="text-[12px] capitalize text-[#898989]">{{ typeLabels[item.type] ?? item.type }}</span>
                </Link>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
