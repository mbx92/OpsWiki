<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { formatMoney } from '@/utils/formatMoney';

defineProps({
    stats: Object,
    recentInvoices: Array,
    recentPayments: Array,
    subscriptions: Array,
});

const page = usePage();
const defaultCurrency = page.props.saas?.defaultCurrency ?? 'USD';

const statusClass = (status) => {
    const map = {
        paid: 'bg-[#dcfce7] text-[#166534]',
        open: 'bg-[#dbeafe] text-[#1e40af]',
        overdue: 'bg-[#fee2e2] text-[#991b1b]',
        void: 'bg-[#f3f4f6] text-[#6b7280]',
        draft: 'bg-[#fef3c7] text-[#92400e]',
        active: 'bg-[#dcfce7] text-[#166534]',
        trialing: 'bg-[#dbeafe] text-[#1e40af]',
        cancelled: 'bg-[#f3f4f6] text-[#6b7280]',
        suspended: 'bg-[#fee2e2] text-[#991b1b]',
    };
    return map[status] ?? 'bg-[#f3f4f6] text-[#6b7280]';
};
</script>

<template>
    <Head title="Platform Billing" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Platform · Billing</h1>
        </template>

        <PlatformNav />

        <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Est. MRR</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ formatMoney(stats.mrr_cents, defaultCurrency, { showZeroAsFree: false }) }}</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Est. ARR</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ formatMoney(stats.arr_cents, defaultCurrency, { showZeroAsFree: false }) }}</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Outstanding</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ formatMoney(stats.outstanding_cents, defaultCurrency, { showZeroAsFree: false }) }}</p>
                <p class="mt-1 text-[12px] text-[#898989]">{{ stats.open_invoices_count }} open · {{ stats.overdue_invoices_count }} overdue</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Collected (this month)</p>
                <p class="mt-1 text-[28px] font-[700] text-[#111111]">{{ formatMoney(stats.paid_this_month_cents, defaultCurrency, { showZeroAsFree: false }) }}</p>
                <p class="mt-1 text-[12px] text-[#898989]">{{ stats.payments_this_month_count }} payments</p>
            </div>
        </div>

        <div class="mb-6 flex flex-wrap gap-2">
            <Link :href="route('platform.invoices.index')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">All invoices</Link>
            <Link :href="route('platform.subscriptions.index')" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[600] text-[#111111] hover:bg-[#f8f9fa]">Subscriptions</Link>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-[16px] font-[600] text-[#111111]">Recent invoices</h2>
                    <Link :href="route('platform.invoices.index')" class="text-[13px] text-[#2563eb] hover:underline">View all</Link>
                </div>
                <div class="space-y-2">
                    <Link
                        v-for="invoice in recentInvoices"
                        :key="invoice.id"
                        :href="route('platform.invoices.show', invoice.id)"
                        class="flex items-center justify-between rounded-[8px] px-3 py-2 hover:bg-[#f8f9fa]"
                    >
                        <div>
                            <p class="text-[14px] font-[500] text-[#111111]">{{ invoice.number }}</p>
                            <p class="text-[12px] text-[#898989]">{{ invoice.tenant?.name }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-[14px] font-[600] text-[#111111]">{{ formatMoney(invoice.total_cents, invoice.currency) }}</p>
                            <span class="inline-block rounded-[9999px] px-2 py-0.5 text-[11px] font-[600] capitalize" :class="statusClass(invoice.status)">{{ invoice.status }}</span>
                        </div>
                    </Link>
                    <p v-if="!recentInvoices.length" class="text-[13px] text-[#898989]">No invoices yet.</p>
                </div>
            </div>

            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <h2 class="mb-4 text-[16px] font-[600] text-[#111111]">Recent payments</h2>
                <div class="space-y-2">
                    <div v-for="payment in recentPayments" :key="payment.id" class="flex items-center justify-between rounded-[8px] px-3 py-2">
                        <div>
                            <p class="text-[14px] font-[500] text-[#111111]">{{ formatMoney(payment.amount_cents, payment.currency) }}</p>
                            <p class="text-[12px] text-[#898989]">{{ payment.tenant?.name }} · {{ payment.invoice?.number }}</p>
                        </div>
                        <p class="text-[12px] text-[#6b7280] capitalize">{{ payment.method?.replace('_', ' ') }}</p>
                    </div>
                    <p v-if="!recentPayments.length" class="text-[13px] text-[#898989]">No payments recorded yet.</p>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-[12px] border border-[#e5e7eb] bg-white p-5">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-[16px] font-[600] text-[#111111]">Subscriptions</h2>
                <Link :href="route('platform.subscriptions.index')" class="text-[13px] text-[#2563eb] hover:underline">Manage all</Link>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-[14px]">
                    <thead class="border-b border-[#e5e7eb] text-[13px] text-[#898989]">
                        <tr>
                            <th class="pb-2 font-[500]">Workspace</th>
                            <th class="pb-2 font-[500]">Plan</th>
                            <th class="pb-2 font-[500]">Interval</th>
                            <th class="pb-2 font-[500]">Status</th>
                            <th class="pb-2 font-[500]">Period end</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sub in subscriptions" :key="sub.id" class="border-b border-[#f3f4f6] last:border-0">
                            <td class="py-2">
                                <Link :href="route('platform.tenants.show', sub.tenant.id)" class="font-[500] text-[#111111] hover:underline">{{ sub.tenant?.name }}</Link>
                            </td>
                            <td class="py-2 capitalize">{{ sub.plan?.name }}</td>
                            <td class="py-2 capitalize">{{ sub.billing_interval }}</td>
                            <td class="py-2">
                                <span class="rounded-[9999px] px-2 py-0.5 text-[11px] font-[600] capitalize" :class="statusClass(sub.status)">{{ sub.status }}</span>
                            </td>
                            <td class="py-2 text-[13px] text-[#6b7280]">{{ sub.current_period_end ? new Date(sub.current_period_end).toLocaleDateString() : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
