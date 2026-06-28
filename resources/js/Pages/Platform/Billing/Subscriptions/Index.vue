<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ subscriptions: Object, plans: Array, filters: Object });

const status = ref(props.filters?.status ?? '');
const plan = ref(props.filters?.plan ?? '');

const search = () => {
    router.get(route('platform.subscriptions.index'), {
        status: status.value || undefined,
        plan: plan.value || undefined,
    }, { preserveState: true });
};

const editingId = ref(null);
const editForm = useForm({
    status: 'active',
    billing_interval: 'monthly',
    billing_email: '',
    trial_ends_at: '',
    current_period_end: '',
    external_customer_id: '',
});

const startEdit = (sub) => {
    editingId.value = sub.id;
    editForm.status = sub.status;
    editForm.billing_interval = sub.billing_interval ?? 'monthly';
    editForm.billing_email = sub.billing_email ?? '';
    editForm.trial_ends_at = sub.trial_ends_at ? sub.trial_ends_at.slice(0, 10) : '';
    editForm.current_period_end = sub.current_period_end ? sub.current_period_end.slice(0, 10) : '';
    editForm.external_customer_id = sub.external_customer_id ?? '';
    editForm.clearErrors();
};

const saveSub = (subId) => {
    editForm.put(route('platform.subscriptions.update', subId), {
        preserveScroll: true,
        onSuccess: () => {
            editingId.value = null;
        },
    });
};

const statusClass = (s) => ({
    active: 'bg-[#dcfce7] text-[#166534]',
    trialing: 'bg-[#dbeafe] text-[#1e40af]',
    cancelled: 'bg-[#f3f4f6] text-[#6b7280]',
    suspended: 'bg-[#fee2e2] text-[#991b1b]',
}[s] ?? 'bg-[#f3f4f6] text-[#6b7280]');
</script>

<template>
    <Head title="Platform Subscriptions" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('platform.billing.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Billing</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Subscriptions</h1>
            </div>
        </template>

        <PlatformNav />

        <form @submit.prevent="search" class="mb-4 flex flex-wrap items-center gap-2">
            <select v-model="status" class="rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[13px]">
                <option value="">All status</option>
                <option value="active">Active</option>
                <option value="trialing">Trialing</option>
                <option value="suspended">Suspended</option>
                <option value="cancelled">Cancelled</option>
            </select>
            <select v-model="plan" class="rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[13px]">
                <option value="">All plans</option>
                <option v-for="p in plans" :key="p.id" :value="p.slug">{{ p.name }}</option>
            </select>
            <button type="submit" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white">Filter</button>
        </form>

        <div class="space-y-4">
            <div v-for="sub in subscriptions.data" :key="sub.id" class="rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <Link :href="route('platform.tenants.show', sub.tenant_id)" class="text-[16px] font-[600] text-[#111111] hover:underline">{{ sub.tenant?.name }}</Link>
                        <p class="mt-1 text-[13px] text-[#898989]">{{ sub.tenant?.slug }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="rounded-[9999px] px-2.5 py-0.5 text-[11px] font-[600] capitalize" :class="statusClass(sub.status)">{{ sub.status }}</span>
                        <button v-if="editingId !== sub.id" type="button" @click="startEdit(sub)" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[12px] hover:bg-[#f8f9fa]">Edit</button>
                    </div>
                </div>

                <div v-if="editingId !== sub.id" class="mt-4 grid gap-3 text-[14px] sm:grid-cols-4">
                    <div><span class="text-[#6b7280]">Plan:</span> {{ sub.plan?.name }}</div>
                    <div><span class="text-[#6b7280]">Interval:</span> <span class="capitalize">{{ sub.billing_interval ?? 'monthly' }}</span></div>
                    <div><span class="text-[#6b7280]">Period end:</span> {{ sub.current_period_end ? new Date(sub.current_period_end).toLocaleDateString() : '—' }}</div>
                    <div><span class="text-[#6b7280]">Trial ends:</span> {{ sub.trial_ends_at ? new Date(sub.trial_ends_at).toLocaleDateString() : '—' }}</div>
                </div>

                <form v-else @submit.prevent="saveSub(sub.id)" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div>
                        <InputLabel value="Status" />
                        <select v-model="editForm.status" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                            <option value="active">Active</option>
                            <option value="trialing">Trialing</option>
                            <option value="suspended">Suspended</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Billing interval" />
                        <select v-model="editForm.billing_interval" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]">
                            <option value="monthly">Monthly</option>
                            <option value="yearly">Yearly</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Billing email" />
                        <input v-model="editForm.billing_email" type="email" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
                    </div>
                    <div>
                        <InputLabel value="Trial ends" />
                        <input v-model="editForm.trial_ends_at" type="date" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
                    </div>
                    <div>
                        <InputLabel value="Period end" />
                        <input v-model="editForm.current_period_end" type="date" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" />
                    </div>
                    <div>
                        <InputLabel value="External customer ID" />
                        <input v-model="editForm.external_customer_id" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" placeholder="Stripe cus_..." />
                    </div>
                    <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-3">
                        <button type="submit" :disabled="editForm.processing" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white">Save</button>
                        <button type="button" @click="editingId = null" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px]">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
