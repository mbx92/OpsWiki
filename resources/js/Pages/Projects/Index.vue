<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import EmptyState from '@/Components/EmptyState.vue';
import StatusBadge from '@/Components/StatusBadge.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ projects: Object, filters: Object, statuses: Array });

const q = ref('');
const search = () => router.get(route('projects.index'), { q: q.value }, { preserveState: true });
</script>

<template>
    <Head title="Projects" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Projects</h1>
                <Link :href="route('projects.create')" data-page-tour="page-actions" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">+ New Project</Link>
            </div>
        </template>

        <form @submit.prevent="search" class="mb-4">
            <input v-model="q" placeholder="Search projects..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]" />
        </form>

        <EmptyState v-if="!projects.data.length" title="No projects yet" description="Track repositories, environments, and deployment notes.">
            <Link :href="route('projects.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[14px] font-[600] text-white">Create project</Link>
        </EmptyState>

        <div v-else class="grid gap-4 sm:grid-cols-2">
            <Link
                v-for="project in projects.data"
                :key="project.id"
                :href="route('projects.show', project.slug)"
                class="rounded-[12px] border border-[#e5e7eb] bg-white p-5 hover:bg-[#f8f9fa]"
            >
                <div class="mb-2 flex items-center justify-between">
                    <h2 class="text-[16px] font-[600] text-[#111111]">{{ project.name }}</h2>
                    <StatusBadge :status="project.status" />
                </div>
                <p v-if="project.description" class="line-clamp-2 text-[13px] text-[#6b7280]">{{ project.description }}</p>
                <p v-if="project.server_location" class="mt-2 text-[12px] text-[#898989]">{{ project.server_location }}</p>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
