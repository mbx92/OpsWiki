<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ tenants: Object, plans: Array, filters: Object });

const q = ref(props.filters?.q ?? '');
const status = ref(props.filters?.status ?? '');
const plan = ref(props.filters?.plan ?? '');

const search = () => {
    router.get(route('platform.tenants.index'), {
        q: q.value || undefined,
        status: status.value || undefined,
        plan: plan.value || undefined,
    }, { preserveState: true });
};
</script>

<template>
    <Head title="Platform Workspaces" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Platform · Workspaces</h1>
        </template>

        <PlatformNav />

        <form @submit.prevent="search" class="mb-4 flex flex-wrap items-center gap-2">
            <input v-model="q" placeholder="Search name or slug..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989] outline-none focus:border-[#111111]" />
            <select v-model="status" class="rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[13px] text-[#374151]">
                <option value="">All status</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
            <select v-model="plan" class="rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[13px] text-[#374151]">
                <option value="">All plans</option>
                <option v-for="p in plans" :key="p.id" :value="p.slug">{{ p.name }}</option>
            </select>
            <button type="submit" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">Filter</button>
        </form>

        <div class="overflow-hidden rounded-[12px] border border-[#e5e7eb] bg-white">
            <table class="w-full text-left text-[14px]">
                <thead class="border-b border-[#e5e7eb] bg-[#f8f9fa] text-[13px] text-[#898989]">
                    <tr>
                        <th class="px-5 py-3 font-[500]">Workspace</th>
                        <th class="px-5 py-3 font-[500]">Plan</th>
                        <th class="px-5 py-3 font-[500]">Users</th>
                        <th class="px-5 py-3 font-[500]">Status</th>
                        <th class="px-5 py-3 font-[500]">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="tenant in tenants.data" :key="tenant.id" class="border-b border-[#f3f4f6] last:border-0 hover:bg-[#f8f9fa]">
                        <td class="px-5 py-3">
                            <Link :href="route('platform.tenants.show', tenant.id)" class="font-[500] text-[#111111] hover:underline">{{ tenant.name }}</Link>
                            <p class="text-[12px] text-[#898989]">{{ tenant.slug }}</p>
                        </td>
                        <td class="px-5 py-3 text-[#374151]">{{ tenant.subscription?.plan?.name ?? '—' }}</td>
                        <td class="px-5 py-3 text-[#374151]">{{ tenant.users_count }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-[8px] px-2 py-0.5 text-[12px] font-[500] capitalize" :class="tenant.status === 'active' ? 'bg-[#ecfdf5] text-[#059669]' : 'bg-[#fef2f2] text-[#dc2626]'">{{ tenant.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-[13px] text-[#898989]">{{ new Date(tenant.created_at).toLocaleDateString() }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="tenants.links?.length > 3" class="mt-4 flex flex-wrap gap-2">
            <Link
                v-for="link in tenants.links"
                :key="link.label"
                :href="link.url"
                class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px]"
                :class="link.active ? 'bg-[#111111] text-white border-[#111111]' : 'bg-white text-[#374151]'"
                v-html="link.label"
            />
        </div>
    </AuthenticatedLayout>
</template>
