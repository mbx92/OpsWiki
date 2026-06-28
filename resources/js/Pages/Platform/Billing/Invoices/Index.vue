<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatMoney } from '@/utils/formatMoney';

const props = defineProps({ invoices: Object, filters: Object, statusOptions: Array });

const q = ref(props.filters?.q ?? '');
const status = ref(props.filters?.status ?? '');

const search = () => {
    router.get(route('platform.invoices.index'), {
        q: q.value || undefined,
        status: status.value || undefined,
    }, { preserveState: true });
};

const statusClass = (s) => ({
    paid: 'bg-[#dcfce7] text-[#166534]',
    open: 'bg-[#dbeafe] text-[#1e40af]',
    overdue: 'bg-[#fee2e2] text-[#991b1b]',
    void: 'bg-[#f3f4f6] text-[#6b7280]',
    draft: 'bg-[#fef3c7] text-[#92400e]',
}[s] ?? 'bg-[#f3f4f6] text-[#6b7280]');
</script>

<template>
    <Head title="Platform Invoices" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('platform.billing.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Billing</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Invoices</h1>
            </div>
        </template>

        <PlatformNav />

        <form @submit.prevent="search" class="mb-4 flex flex-wrap items-center gap-2">
            <input v-model="q" placeholder="Search invoice or workspace..." class="w-full max-w-sm rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
            <select v-model="status" class="rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[13px]">
                <option value="">All status</option>
                <option v-for="s in statusOptions" :key="s" :value="s">{{ s }}</option>
            </select>
            <button type="submit" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white">Filter</button>
        </form>

        <div class="overflow-hidden rounded-[12px] border border-[#e5e7eb] bg-white">
            <table class="w-full text-left text-[14px]">
                <thead class="border-b border-[#e5e7eb] bg-[#f8f9fa] text-[13px] text-[#898989]">
                    <tr>
                        <th class="px-5 py-3 font-[500]">Invoice</th>
                        <th class="px-5 py-3 font-[500]">Workspace</th>
                        <th class="px-5 py-3 font-[500]">Amount</th>
                        <th class="px-5 py-3 font-[500]">Status</th>
                        <th class="px-5 py-3 font-[500]">Due</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="invoice in invoices.data" :key="invoice.id" class="border-b border-[#f3f4f6] last:border-0 hover:bg-[#f8f9fa]">
                        <td class="px-5 py-3">
                            <Link :href="route('platform.invoices.show', invoice.id)" class="font-[500] text-[#111111] hover:underline">{{ invoice.number }}</Link>
                        </td>
                        <td class="px-5 py-3">
                            <Link :href="route('platform.tenants.show', invoice.tenant.id)" class="hover:underline">{{ invoice.tenant?.name }}</Link>
                        </td>
                        <td class="px-5 py-3 font-[600]">{{ formatMoney(invoice.total_cents, invoice.currency) }}</td>
                        <td class="px-5 py-3">
                            <span class="rounded-[9999px] px-2 py-0.5 text-[11px] font-[600] capitalize" :class="statusClass(invoice.status)">{{ invoice.status }}</span>
                        </td>
                        <td class="px-5 py-3 text-[13px] text-[#6b7280]">{{ invoice.due_at ? new Date(invoice.due_at).toLocaleDateString() : '—' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
