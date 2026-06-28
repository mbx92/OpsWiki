<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import TagBadge from '@/Components/TagBadge.vue';
import RelatedItemsList from '@/Components/RelatedItemsList.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({ project: Object, related: Array });

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
                <div class="flex items-center gap-3">
                    <Link :href="route('projects.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Projects</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ project.name }}</h1>
                    <StatusBadge :status="project.status" />
                </div>
                <div class="flex gap-2">
                    <Link :href="route('projects.edit', project.slug)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] text-[#374151]">Edit</Link>
                    <button @click="destroy" class="rounded-[8px] border border-[#fecaca] px-3 py-1.5 text-[13px] font-[500] text-[#ef4444]">Delete</button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-3xl space-y-4">
            <div v-if="project.tags?.length" class="flex flex-wrap gap-2">
                <TagBadge v-for="tag in project.tags" :key="tag.id" :name="tag.name" />
            </div>

            <div v-if="project.description" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <h2 class="mb-2 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Description</h2>
                <p class="whitespace-pre-wrap text-[15px] text-[#374151]">{{ project.description }}</p>
            </div>

            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <h2 class="mb-3 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Links</h2>
                <dl class="space-y-2 text-[14px]">
                    <div v-if="project.repository_url" class="flex gap-2"><dt class="w-32 text-[#898989]">Repository</dt><dd><a :href="project.repository_url" target="_blank" class="text-[#111111] hover:underline">{{ project.repository_url }}</a></dd></div>
                    <div v-if="project.production_url" class="flex gap-2"><dt class="w-32 text-[#898989]">Production</dt><dd><a :href="project.production_url" target="_blank" class="text-[#111111] hover:underline">{{ project.production_url }}</a></dd></div>
                    <div v-if="project.staging_url" class="flex gap-2"><dt class="w-32 text-[#898989]">Staging</dt><dd><a :href="project.staging_url" target="_blank" class="text-[#111111] hover:underline">{{ project.staging_url }}</a></dd></div>
                    <div v-if="project.server_location" class="flex gap-2"><dt class="w-32 text-[#898989]">Server</dt><dd>{{ project.server_location }}</dd></div>
                </dl>
            </div>

            <div v-if="project.environment_notes" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <h2 class="mb-2 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Environment notes</h2>
                <p class="whitespace-pre-wrap text-[15px] text-[#374151]">{{ project.environment_notes }}</p>
            </div>

            <RelatedItemsList :items="related" />
        </div>
    </AuthenticatedLayout>
</template>
