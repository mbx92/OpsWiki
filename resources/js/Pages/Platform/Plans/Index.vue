<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/formatMoney';

defineProps({ plans: Array });
</script>

<template>
    <Head title="Platform Plans" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Platform · Pricing</h1>
        </template>

        <PlatformNav />
        <div class="space-y-3">
            <Link v-for="plan in plans" :key="plan.id" :href="route('platform.plans.edit', plan.id)" class="block rounded-[12px] border border-[#e5e7eb] bg-white px-5 py-4 hover:bg-[#f8f9fa]">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-[15px] font-[600] text-[#111111]">{{ plan.name }}</p>
                        <p class="mt-1 text-[13px] text-[#6b7280]">{{ plan.description }}</p>
                    </div>
                    <div class="text-right text-[13px] text-[#374151]">
                        <p>{{ formatPrice(plan.price_monthly_cents, plan.currency) }}<span v-if="plan.price_monthly_cents">/mo</span></p>
                        <p class="text-[#898989]">{{ plan.is_active ? 'Active' : 'Inactive' }}</p>
                    </div>
                </div>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
