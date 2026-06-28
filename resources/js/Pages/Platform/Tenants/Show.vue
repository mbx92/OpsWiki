<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { formatMoney } from '@/utils/formatMoney';

const props = defineProps({ tenant: Object, usage: Object, plans: Array, recentInvoices: Array });

const form = useForm({
    name: props.tenant.name,
    status: props.tenant.status,
    plan_id: props.tenant.subscription?.plan_id ?? props.plans[0]?.id,
    subscription_status: props.tenant.subscription?.status ?? 'active',
    billing_interval: props.tenant.subscription?.billing_interval ?? 'monthly',
    billing_email: props.tenant.subscription?.billing_email ?? '',
    trial_ends_at: props.tenant.subscription?.trial_ends_at?.slice(0, 10) ?? '',
    current_period_end: props.tenant.subscription?.current_period_end?.slice(0, 10) ?? '',
});

const invoiceForm = useForm({
    billing_interval: props.tenant.subscription?.billing_interval ?? 'monthly',
    notes: '',
});

const submit = () => form.put(route('platform.tenants.update', props.tenant.id));

const createInvoice = () => {
    invoiceForm.post(route('platform.tenants.invoices.store', props.tenant.id), {
        preserveScroll: true,
        onSuccess: () => invoiceForm.reset('notes'),
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
    <Head :title="`Workspace: ${tenant.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('platform.tenants.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Workspaces</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ tenant.name }}</h1>
                </div>
                <FormHeaderActions form-id="tenant-form" :cancel-href="route('platform.tenants.index')" save-label="Save" :processing="form.processing" />
            </div>
        </template>

        <PlatformNav />

        <div class="mb-6 grid gap-4 sm:grid-cols-3">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Users</p>
                <p class="mt-1 text-[24px] font-[700] text-[#111111]">{{ usage.users }}</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Wiki pages</p>
                <p class="mt-1 text-[24px] font-[700] text-[#111111]">{{ usage.pages }}</p>
            </div>
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <p class="text-[13px] text-[#6b7280]">Slug</p>
                <p class="mt-1 text-[16px] font-[600] text-[#111111]">{{ tenant.slug }}</p>
                <a :href="route('portal.tenant', tenant.slug)" target="_blank" class="mt-1 inline-block text-[13px] text-[#2563eb] hover:underline">Open portal</a>
            </div>
        </div>

        <form id="tenant-form" @submit.prevent="submit" class="mb-6 rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-4">
            <h2 class="text-[16px] font-[600] text-[#111111]">Workspace & subscription</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel value="Name" />
                    <input v-model="form.name" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" required />
                </div>
                <div>
                    <InputLabel value="Workspace status" />
                    <select v-model="form.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Plan" />
                    <select v-model="form.plan_id" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Subscription status" />
                    <select v-model="form.subscription_status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                        <option value="active">Active</option>
                        <option value="trialing">Trialing</option>
                        <option value="suspended">Suspended</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Billing interval" />
                    <select v-model="form.billing_interval" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                <div>
                    <InputLabel value="Billing email" />
                    <input v-model="form.billing_email" type="email" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Trial ends" />
                    <input v-model="form.trial_ends_at" type="date" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
                </div>
                <div>
                    <InputLabel value="Current period end" />
                    <input v-model="form.current_period_end" type="date" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
                </div>
            </div>
        </form>

        <div class="mb-6 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <h2 class="text-[16px] font-[600] text-[#111111]">Billing</h2>
                <Link :href="route('platform.billing.index')" class="text-[13px] text-[#2563eb] hover:underline">Billing overview</Link>
            </div>
            <form @submit.prevent="createInvoice" class="mb-4 flex flex-wrap items-end gap-2 rounded-[8px] bg-[#f8f9fa] p-4">
                <div>
                    <InputLabel value="New invoice interval" />
                    <select v-model="invoiceForm.billing_interval" class="mt-1.5 rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                        <option value="one_time">One-time</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <InputLabel value="Notes (optional)" />
                    <input v-model="invoiceForm.notes" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
                </div>
                <button type="submit" :disabled="invoiceForm.processing" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50">Create invoice</button>
            </form>
            <div v-if="recentInvoices.length" class="space-y-2">
                <Link
                    v-for="invoice in recentInvoices"
                    :key="invoice.id"
                    :href="route('platform.invoices.show', invoice.id)"
                    class="flex items-center justify-between rounded-[8px] border border-[#f3f4f6] px-4 py-3 hover:bg-[#f8f9fa]"
                >
                    <span class="text-[14px] font-[500] text-[#111111]">{{ invoice.number }}</span>
                    <div class="flex items-center gap-3">
                        <span class="text-[14px] font-[600]">{{ formatMoney(invoice.total_cents, invoice.currency) }}</span>
                        <span class="rounded-[9999px] px-2 py-0.5 text-[11px] font-[600] capitalize" :class="statusClass(invoice.status)">{{ invoice.status }}</span>
                    </div>
                </Link>
            </div>
            <p v-else class="text-[13px] text-[#898989]">No invoices for this workspace.</p>
        </div>

        <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="mb-4 text-[16px] font-[600] text-[#111111]">Members</h2>
            <div class="space-y-2">
                <div v-for="user in tenant.users" :key="user.id" class="flex items-center justify-between rounded-[8px] border border-[#f3f4f6] px-4 py-3">
                    <div>
                        <p class="text-[14px] font-[500] text-[#111111]">{{ user.name }}</p>
                        <p class="text-[13px] text-[#898989]">{{ user.email }}</p>
                    </div>
                    <div class="text-right text-[13px] text-[#6b7280]">
                        <p class="capitalize">{{ user.pivot?.role ?? 'member' }}</p>
                        <p>{{ user.role?.name ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
