<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePlanFeatures } from '@/Composables/usePlanFeatures';
import { Head, Link } from '@inertiajs/vue3';
import { formatMoney } from '@/utils/formatMoney';

defineProps({ tenant: Object, invoices: Array });

const { plan, upgradeUrl } = usePlanFeatures();

const statusClass = (s) => ({
    paid: 'bg-[#dcfce7] text-[#166534]',
    open: 'bg-[#dbeafe] text-[#1e40af]',
    overdue: 'bg-[#fee2e2] text-[#991b1b]',
    void: 'bg-[#f3f4f6] text-[#6b7280]',
}[s] ?? 'bg-[#f3f4f6] text-[#6b7280]');
</script>

<template>
    <Head title="Billing" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Billing</h1>
            </div>
        </template>

        <div
            v-if="plan?.slug === 'free' || (!plan?.god_mode && tenant.subscription?.plan?.slug === 'free')"
            class="mb-6 rounded-[12px] border border-[#111111] bg-[#111111] p-6 text-white"
        >
            <h2 class="text-[18px] font-[700]" style="font-family: 'Manrope', sans-serif;">Unlock Pro features</h2>
            <p class="mt-2 text-[14px] text-[#d1d5db]">Books, SOPs, AI assistant, integrations, unlimited users & pages, and more.</p>
            <Link :href="route('upgrade.pro')" class="mt-4 inline-flex rounded-[8px] bg-white px-4 py-2 text-[13px] font-[600] text-[#111111] hover:bg-[#f3f4f6]">
                View Pro plan →
            </Link>
        </div>

        <div class="mb-6 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="text-[16px] font-[600] text-[#111111]">Current subscription</h2>
            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4 text-[14px]">
                <div>
                    <p class="text-[#6b7280]">Plan</p>
                    <p class="mt-1 font-[600] text-[#111111]">{{ tenant.subscription?.plan?.name ?? 'Free' }}</p>
                </div>
                <div>
                    <p class="text-[#6b7280]">Status</p>
                    <p class="mt-1 capitalize font-[600] text-[#111111]">{{ tenant.subscription?.status ?? 'active' }}</p>
                </div>
                <div>
                    <p class="text-[#6b7280]">Billing interval</p>
                    <p class="mt-1 capitalize font-[600] text-[#111111]">{{ tenant.subscription?.billing_interval ?? 'monthly' }}</p>
                </div>
                <div>
                    <p class="text-[#6b7280]">Current period ends</p>
                    <p class="mt-1 font-[600] text-[#111111]">
                        {{ tenant.subscription?.current_period_end ? new Date(tenant.subscription.current_period_end).toLocaleDateString() : '—' }}
                    </p>
                </div>
            </div>
            <p class="mt-4 text-[13px] text-[#6b7280]">Contact your platform administrator to change plan or payment method.</p>
        </div>

        <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="mb-4 text-[16px] font-[600] text-[#111111]">Invoice history</h2>
            <div v-if="invoices.length" class="overflow-hidden rounded-[8px] border border-[#f3f4f6]">
                <table class="w-full text-left text-[14px]">
                    <thead class="border-b border-[#e5e7eb] bg-[#f8f9fa] text-[13px] text-[#898989]">
                        <tr>
                            <th class="px-4 py-3 font-[500]">Invoice</th>
                            <th class="px-4 py-3 font-[500]">Amount</th>
                            <th class="px-4 py-3 font-[500]">Status</th>
                            <th class="px-4 py-3 font-[500]">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invoice in invoices" :key="invoice.id" class="border-b border-[#f3f4f6] last:border-0">
                            <td class="px-4 py-3 font-[500]">{{ invoice.number }}</td>
                            <td class="px-4 py-3">{{ formatMoney(invoice.total_cents, invoice.currency) }}</td>
                            <td class="px-4 py-3">
                                <span class="rounded-[9999px] px-2 py-0.5 text-[11px] font-[600] capitalize" :class="statusClass(invoice.status)">{{ invoice.status }}</span>
                            </td>
                            <td class="px-4 py-3 text-[13px] text-[#6b7280]">{{ invoice.due_at ? new Date(invoice.due_at).toLocaleDateString() : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p v-else class="text-[13px] text-[#898989]">No invoices yet.</p>
        </div>
    </AuthenticatedLayout>
</template>
