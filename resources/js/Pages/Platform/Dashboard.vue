<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { formatMoney } from '@/utils/formatMoney';

defineProps({
    stats: Object,
    planBreakdown: Array,
    recentTenants: Array,
});

const page = usePage();
const defaultCurrency = page.props.saas?.defaultCurrency ?? 'USD';

const formatMrr = (cents) => formatMoney(cents ?? 0, defaultCurrency, { showZeroAsFree: false });
</script>

<template>
    <Head title="Platform Dashboard" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Platform Admin</h1>
        </template>

        <PlatformNav />

        <div class="mb-6 flex flex-wrap gap-2">
            <Link :href="route('platform.billing.index')" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[600] text-[#111111] hover:bg-[#f8f9fa]">Billing overview →</Link>
        </div>

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Workspaces</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ stats.tenants_total }}</p>
                <p class="mt-1 text-[12px] text-[#898989]">{{ stats.tenants_active }} active · {{ stats.tenants_suspended }} suspended</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Users</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ stats.users_total }}</p>
                <p class="mt-1 text-[12px] text-[#898989]">{{ stats.super_admins }} super admins</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Est. MRR</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ formatMrr(stats.mrr_cents) }}</p>
                <p class="mt-1 text-[12px] text-[#898989]">Active paid subscriptions</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Signups (30d)</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ stats.signups_30d }}</p>
                <p class="mt-1 text-[12px] text-[#898989]">New workspaces</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <h2 class="text-[16px] font-[600] text-[#111111]">Plan distribution</h2>
                <div class="mt-4 space-y-3">
                    <div v-for="row in planBreakdown" :key="row.plan.id" class="flex items-center justify-between text-[14px]">
                        <span class="text-[#374151]">{{ row.plan.name }}</span>
                        <span class="font-[600] text-[#111111]">{{ row.count }} workspace{{ row.count === 1 ? '' : 's' }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-[16px] font-[600] text-[#111111]">Recent workspaces</h2>
                    <Link :href="route('platform.tenants.index')" class="text-[13px] text-[#2563eb] hover:underline">View all</Link>
                </div>
                <div class="space-y-2">
                    <Link
                        v-for="tenant in recentTenants"
                        :key="tenant.id"
                        :href="route('platform.tenants.show', tenant.id)"
                        class="flex items-center justify-between rounded-[8px] px-3 py-2 hover:bg-[#f8f9fa]"
                    >
                        <div>
                            <p class="text-[14px] font-[500] text-[#111111]">{{ tenant.name }}</p>
                            <p class="text-[12px] text-[#898989]">{{ tenant.slug }} · {{ tenant.users_count }} users</p>
                        </div>
                        <span class="text-[12px] capitalize text-[#6b7280]">{{ tenant.plan?.name ?? '—' }}</span>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
