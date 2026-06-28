<script setup>
import { Link } from '@inertiajs/vue3';

const items = [
    { label: 'Dashboard', route: 'platform.dashboard', match: ['platform.dashboard'] },
    { label: 'Workspaces', route: 'platform.tenants.index', match: ['platform.tenants.*'] },
    { label: 'Billing', route: 'platform.billing.index', match: ['platform.billing.*', 'platform.invoices.*', 'platform.subscriptions.*'] },
    { label: 'Pricing', route: 'platform.plans.index', match: ['platform.plans.*'] },
    { label: 'Policies', route: 'platform.policies.index', match: ['platform.policies.*'] },
    { label: 'Super Admins', route: 'platform.super-admins.index', match: ['platform.super-admins.*'] },
];

const isActive = (item) => item.match.some((pattern) => route().current(pattern));
</script>

<template>
    <nav class="mb-6 flex flex-wrap gap-2 border-b border-[#e5e7eb] pb-3">
        <Link
            v-for="item in items"
            :key="item.route"
            :href="route(item.route)"
            class="inline-flex items-center rounded-[8px] px-3 py-2 text-[13px] font-[500] transition-colors"
            :class="isActive(item) ? 'bg-[#111111] text-white' : 'text-[#374151] hover:bg-[#f8f9fa]'"
        >
            {{ item.label }}
        </Link>
    </nav>
</template>
