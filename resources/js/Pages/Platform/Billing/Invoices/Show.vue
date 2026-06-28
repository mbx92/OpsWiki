<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { formatMoney } from '@/utils/formatMoney';

const props = defineProps({ invoice: Object, payments: Array });

const paymentForm = useForm({
    amount_cents: props.invoice.balance_cents || props.invoice.total_cents,
    method: 'manual',
    reference: '',
    notes: '',
});

const recordPayment = () => {
    paymentForm.post(route('platform.invoices.payments.store', props.invoice.id), {
        preserveScroll: true,
        onSuccess: () => {
            paymentForm.reset('reference', 'notes');
        },
    });
};

const voidInvoice = () => {
    if (!confirm('Void this invoice?')) return;
    useForm({}).post(route('platform.invoices.void', props.invoice.id), {
        preserveScroll: true,
    });
};

const statusClass = (s) => ({
    paid: 'bg-[#dcfce7] text-[#166534]',
    open: 'bg-[#dbeafe] text-[#1e40af]',
    overdue: 'bg-[#fee2e2] text-[#991b1b]',
    void: 'bg-[#f3f4f6] text-[#6b7280]',
}[s] ?? 'bg-[#f3f4f6] text-[#6b7280]');
</script>

<template>
    <Head :title="`Invoice ${invoice.number}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('platform.invoices.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Invoices</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ invoice.number }}</h1>
                    <span class="rounded-[9999px] px-2.5 py-0.5 text-[11px] font-[600] capitalize" :class="statusClass(invoice.status)">{{ invoice.status }}</span>
                </div>
                <button
                    v-if="invoice.status !== 'paid' && invoice.status !== 'void'"
                    type="button"
                    @click="voidInvoice"
                    class="rounded-[8px] border border-[#fecaca] px-4 py-2 text-[13px] font-[600] text-[#991b1b] hover:bg-[#fef2f2]"
                >
                    Void invoice
                </button>
            </div>
        </template>

        <PlatformNav />

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Total</p>
                <p class="mt-1 text-[24px] font-[700] text-[#111111]">{{ formatMoney(invoice.total_cents, invoice.currency) }}</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Paid</p>
                <p class="mt-1 text-[24px] font-[700] text-[#111111]">{{ formatMoney(invoice.paid_cents, invoice.currency) }}</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Balance</p>
                <p class="mt-1 text-[24px] font-[700] text-[#111111]">{{ formatMoney(invoice.balance_cents, invoice.currency) }}</p>
            </div>
        </div>

        <div class="mb-6 grid gap-6 lg:grid-cols-2">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <h2 class="text-[16px] font-[600] text-[#111111]">Details</h2>
                <dl class="mt-4 space-y-3 text-[14px]">
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#6b7280]">Workspace</dt>
                        <dd>
                            <Link :href="route('platform.tenants.show', invoice.tenant.id)" class="font-[500] text-[#2563eb] hover:underline">{{ invoice.tenant?.name }}</Link>
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#6b7280]">Plan</dt>
                        <dd>{{ invoice.plan?.name ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#6b7280]">Billing interval</dt>
                        <dd class="capitalize">{{ invoice.billing_interval }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#6b7280]">Due date</dt>
                        <dd>{{ invoice.due_at ? new Date(invoice.due_at).toLocaleDateString() : '—' }}</dd>
                    </div>
                </dl>
                <div v-if="invoice.line_items?.length" class="mt-6">
                    <h3 class="text-[13px] font-[600] text-[#898989] uppercase">Line items</h3>
                    <ul class="mt-2 space-y-2 text-[14px]">
                        <li v-for="(item, i) in invoice.line_items" :key="i" class="flex justify-between">
                            <span>{{ item.description }}</span>
                            <span class="font-[500]">{{ formatMoney(item.amount_cents, invoice.currency) }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div v-if="invoice.balance_cents > 0 && invoice.status !== 'void'" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <h2 class="text-[16px] font-[600] text-[#111111]">Record payment</h2>
                <form @submit.prevent="recordPayment" class="mt-4 space-y-4">
                    <div>
                        <InputLabel value="Amount (cents)" />
                        <input v-model.number="paymentForm.amount_cents" type="number" min="1" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" required />
                    </div>
                    <div>
                        <InputLabel value="Method" />
                        <select v-model="paymentForm.method" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                            <option value="manual">Manual</option>
                            <option value="bank_transfer">Bank transfer</option>
                            <option value="card">Card</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Reference" />
                        <input v-model="paymentForm.reference" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" placeholder="Transaction ID..." />
                    </div>
                    <button type="submit" :disabled="paymentForm.processing" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50">Record payment</button>
                </form>
            </div>
        </div>

        <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="mb-4 text-[16px] font-[600] text-[#111111]">Payment history</h2>
            <div v-if="payments.length" class="space-y-2">
                <div v-for="payment in payments" :key="payment.id" class="flex items-center justify-between rounded-[8px] border border-[#f3f4f6] px-4 py-3 text-[14px]">
                    <div>
                        <p class="font-[500] text-[#111111]">{{ formatMoney(payment.amount_cents, payment.currency) }}</p>
                        <p class="text-[12px] text-[#898989] capitalize">{{ payment.method?.replace('_', ' ') }} · {{ payment.recorded_by?.name ?? 'System' }}</p>
                    </div>
                    <p class="text-[13px] text-[#6b7280]">{{ payment.paid_at ? new Date(payment.paid_at).toLocaleString() : '—' }}</p>
                </div>
            </div>
            <p v-else class="text-[13px] text-[#898989]">No payments yet.</p>
        </div>
    </AuthenticatedLayout>
</template>
