<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TagBadge from '@/Components/TagBadge.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    project: Object,
    documentation: Object,
    quickCreate: Array,
    canManage: Boolean,
});

const statLabels = {
    pages: 'Wiki',
    sops: 'SOP',
    troubleshooting_cases: 'Troubleshooting',
    snippets: 'Snippets',
    tools: 'Tools',
};

const stats = computed(() => {
    const raw = props.documentation?.stats ?? {};
    return Object.entries(raw)
        .filter(([, count]) => count > 0)
        .map(([key, count]) => ({
            key,
            label: statLabels[key] ?? key,
            count,
        }));
});

const docGroups = computed(() => props.documentation?.groups ?? []);
const totalDocs = computed(() => props.documentation?.total ?? 0);

const statSummary = computed(() => {
    if (totalDocs.value === 0) {
        return 'Belum ada dokumentasi';
    }

    const parts = stats.value.map((s) => `${s.count} ${s.label}`);
    return `${totalDocs.value} dokumen · ${parts.join(' · ')}`;
});

const infraSections = computed(() => [
    { key: 'environment_notes', label: 'Environment', value: props.project.environment_notes },
    { key: 'deployment_notes', label: 'Deployment', value: props.project.deployment_notes },
    { key: 'database_notes', label: 'Database', value: props.project.database_notes },
    { key: 'backup_notes', label: 'Backup & restore', value: props.project.backup_notes },
].filter((section) => section.value));

const hasLinks = computed(() =>
    props.project.repository_url
    || props.project.production_url
    || props.project.staging_url
    || props.project.server_location,
);

const destroy = async () => {
    if (await confirmDelete('This project will be permanently deleted.', 'Delete this project?')) {
        router.delete(route('projects.destroy', props.project.slug));
    }
};
</script>

<template>
    <Head :title="project.name" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <Link :href="route('projects.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Projects</Link>
                        <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ project.name }}</h1>
                        <StatusBadge :status="project.status" />
                    </div>
                    <p class="mt-1 text-[13px] text-[#898989]" data-page-tour="project-doc-stats">{{ statSummary }}</p>
                </div>
                <div class="flex gap-2" data-page-tour="page-actions">
                    <template v-if="canManage">
                        <Link :href="route('projects.edit', project.slug)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">Edit</Link>
                        <button @click="destroy" class="rounded-[8px] border border-[#fecaca] px-3 py-1.5 text-[13px] font-[500] text-[#ef4444]">Delete</button>
                    </template>
                </div>
            </div>
        </template>

        <div class="grid gap-6 lg:grid-cols-12" data-page-tour="page-content">
            <div class="space-y-4 lg:col-span-8">
                <div v-if="project.tags?.length" class="flex flex-wrap gap-2">
                    <TagBadge v-for="tag in project.tags" :key="tag.id" :name="tag.name" />
                </div>

                <div v-if="project.description" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                    <h2 class="mb-2 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Overview</h2>
                    <p class="whitespace-pre-wrap text-[15px] leading-relaxed text-[#374151]">{{ project.description }}</p>
                </div>

                <div
                    v-if="docGroups.length"
                    class="rounded-[12px] border border-[#e5e7eb] bg-white p-6"
                    data-page-tour="project-doc-groups"
                >
                    <h2 class="mb-5 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Dokumentasi</h2>
                    <div class="divide-y divide-[#f3f4f6]">
                        <section
                            v-for="group in docGroups"
                            :key="group.type"
                            class="py-5 first:pt-0 last:pb-0"
                        >
                            <div class="mb-3 flex items-center justify-between gap-2">
                                <h3 class="text-[15px] font-[600] text-[#111111]">{{ group.label }}</h3>
                                <span class="rounded-[9999px] bg-[#f3f4f6] px-2 py-0.5 text-[12px] font-[500] text-[#6b7280]">{{ group.items.length }}</span>
                            </div>
                            <div class="space-y-1.5">
                                <a
                                    v-for="item in group.items"
                                    :key="`${group.type}-${item.id}`"
                                    :href="item.url"
                                    class="flex items-center rounded-[8px] px-3 py-2.5 text-[14px] transition-colors hover:bg-[#f8f9fa]"
                                >
                                    <span class="font-[500] text-[#111111]">{{ item.title }}</span>
                                </a>
                            </div>
                        </section>
                    </div>
                </div>

                <div v-else class="rounded-[12px] border border-dashed border-[#e5e7eb] bg-white p-10 text-center">
                    <p class="text-[15px] font-[500] text-[#374151]">Belum ada dokumentasi terkait</p>
                    <p class="mt-1 text-[13px] text-[#898989]">
                        <template v-if="quickCreate.length">Gunakan Quick create di panel kanan untuk mulai mendokumentasikan sistem ini.</template>
                        <template v-else>Tautkan Wiki, SOP, Snippet, atau Troubleshooting ke proyek ini dari halaman edit masing-masing modul.</template>
                    </p>
                </div>
            </div>

            <aside class="space-y-4 lg:col-span-4 lg:sticky lg:top-24 lg:self-start">
                <div v-if="quickCreate.length" class="rounded-[12px] border border-[#e5e7eb] bg-white p-5" data-page-tour="project-quick-create">
                    <h2 class="mb-1 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Quick create</h2>
                    <p class="mb-4 text-[12px] text-[#898989]">Otomatis ditautkan ke proyek ini</p>
                    <div class="flex flex-col gap-2">
                        <Link
                            v-for="action in quickCreate"
                            :key="action.key"
                            :href="route(action.route, action.params)"
                            class="rounded-[8px] border border-[#e5e7eb] px-3 py-2.5 text-[13px] font-[500] text-[#374151] transition-colors hover:border-[#111111] hover:bg-[#f8f9fa]"
                        >
                            + {{ action.label }}
                        </Link>
                    </div>
                </div>

                <div v-if="hasLinks" class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                    <h2 class="mb-3 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Links</h2>
                    <dl class="space-y-3 text-[13px]">
                        <div v-if="project.repository_url">
                            <dt class="text-[#898989]">Repository</dt>
                            <dd class="mt-0.5 break-all"><a :href="project.repository_url" target="_blank" rel="noopener" class="text-[#111111] hover:underline">{{ project.repository_url }}</a></dd>
                        </div>
                        <div v-if="project.production_url">
                            <dt class="text-[#898989]">Production</dt>
                            <dd class="mt-0.5 break-all"><a :href="project.production_url" target="_blank" rel="noopener" class="text-[#111111] hover:underline">{{ project.production_url }}</a></dd>
                        </div>
                        <div v-if="project.staging_url">
                            <dt class="text-[#898989]">Staging</dt>
                            <dd class="mt-0.5 break-all"><a :href="project.staging_url" target="_blank" rel="noopener" class="text-[#111111] hover:underline">{{ project.staging_url }}</a></dd>
                        </div>
                        <div v-if="project.server_location">
                            <dt class="text-[#898989]">Server</dt>
                            <dd class="mt-0.5 text-[#374151]">{{ project.server_location }}</dd>
                        </div>
                    </dl>
                </div>

                <div
                    v-if="infraSections.length"
                    class="rounded-[12px] border border-[#e5e7eb] bg-white p-5"
                    data-page-tour="project-infra-notes"
                >
                    <h2 class="mb-4 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Infrastruktur</h2>
                    <div class="space-y-4">
                        <div v-for="section in infraSections" :key="section.key">
                            <h3 class="text-[13px] font-[600] text-[#374151]">{{ section.label }}</h3>
                            <p class="mt-1 whitespace-pre-wrap text-[13px] leading-relaxed text-[#6b7280]">{{ section.value }}</p>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
