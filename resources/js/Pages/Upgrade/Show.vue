<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { formatPrice } from '@/utils/formatMoney';

const props = defineProps({
    targetPlan: Object,
    currentPlan: Object,
    comparisonPlan: Object,
    alreadyIncluded: Boolean,
    requestedFeature: String,
    requestedFeatureLabel: String,
    includedGates: Array,
    includedOnAllPlans: Array,
});

const isPro = props.targetPlan.slug === 'pro';
</script>

<template>
    <Head :title="`Upgrade to ${targetPlan.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('dashboard')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Dashboard</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">
                    Upgrade to {{ targetPlan.name }}
                </h1>
            </div>
        </template>

        <div v-if="alreadyIncluded" class="rounded-[12px] border border-[#bbf7d0] bg-[#f0fdf4] p-6">
            <h2 class="text-[18px] font-[700] text-[#166534]">You're already on {{ currentPlan?.name ?? 'a higher plan' }}</h2>
            <p class="mt-2 text-[14px] text-[#166534]">
                Your workspace already includes everything in {{ targetPlan.name }}.
            </p>
            <Link :href="route('dashboard')" class="mt-4 inline-flex rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">
                Back to dashboard
            </Link>
        </div>

        <template v-else>
            <div
                v-if="requestedFeatureLabel"
                class="mb-6 rounded-[12px] border border-[#fde68a] bg-[#fef3c7] px-5 py-4 text-[14px] text-[#92400e]"
            >
                <strong>{{ requestedFeatureLabel }}</strong> requires the {{ targetPlan.name }} plan.
                Upgrade below to unlock it for your workspace.
            </div>

            <div class="grid gap-6 lg:grid-cols-5">
                <!-- Plan card -->
                <div class="lg:col-span-2">
                    <div class="sticky top-24 rounded-[12px] border border-[#111111] bg-white p-8 shadow-sm">
                        <div v-if="targetPlan.is_popular" class="mb-3 inline-block rounded-[9999px] bg-[#111111] px-2.5 py-0.5 text-[11px] font-[600] text-white">
                            Popular
                        </div>
                        <h2 class="text-[28px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">{{ targetPlan.name }}</h2>
                        <p class="mt-2 text-[14px] text-[#6b7280]">{{ targetPlan.description }}</p>
                        <p class="mt-6 text-[36px] font-[700] text-[#111111]">
                            {{ formatPrice(targetPlan.price_monthly_cents, targetPlan.currency) }}
                            <span v-if="targetPlan.price_monthly_cents" class="text-[14px] font-[500] text-[#6b7280]">/mo</span>
                        </p>
                        <p v-if="targetPlan.price_yearly_cents" class="mt-1 text-[13px] text-[#6b7280]">
                            or {{ formatPrice(targetPlan.price_yearly_cents, targetPlan.currency) }}/year
                        </p>

                        <div class="mt-6 rounded-[8px] bg-[#f8f9fa] px-4 py-3 text-[13px] text-[#374151]">
                            Current plan: <strong>{{ currentPlan?.name ?? 'Free' }}</strong>
                        </div>

                        <Link
                            :href="route('settings.billing')"
                            class="mt-6 flex h-[44px] w-full items-center justify-center rounded-[8px] bg-[#111111] text-[14px] font-[600] text-white hover:bg-[#242424]"
                        >
                            Request {{ targetPlan.name }} upgrade
                        </Link>
                        <p class="mt-3 text-center text-[12px] text-[#898989]">
                            Contact your platform admin or complete payment via billing.
                        </p>

                        <Link :href="route('settings.billing')" class="mt-4 block text-center text-[13px] text-[#2563eb] hover:underline">
                            View billing & invoices →
                        </Link>
                    </div>
                </div>

                <!-- Features -->
                <div class="lg:col-span-3 space-y-6">
                    <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                        <h3 class="text-[16px] font-[600] text-[#111111]">Everything in {{ targetPlan.name }}</h3>
                        <ul class="mt-4 space-y-2">
                            <li v-for="(feature, i) in targetPlan.features" :key="i" class="flex items-start gap-2 text-[14px] text-[#374151]">
                                <span class="mt-0.5 text-[#10b981]">✓</span>
                                {{ feature }}
                            </li>
                        </ul>
                    </div>

                    <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                        <h3 class="text-[16px] font-[600] text-[#111111]">Unlocked modules</h3>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div
                                v-for="gate in includedGates"
                                :key="gate.key"
                                class="rounded-[8px] border border-[#f3f4f6] px-4 py-3"
                            >
                                <p class="text-[14px] font-[500] text-[#111111]">{{ gate.label }}</p>
                                <p class="mt-1 text-[12px] text-[#6b7280]">{{ gate.description }}</p>
                            </div>
                        </div>
                    </div>

                    <div v-if="comparisonPlan && isPro" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                        <h3 class="text-[16px] font-[600] text-[#111111]">Free vs Pro</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2 text-[14px]">
                            <div class="rounded-[8px] bg-[#f8f9fa] p-4">
                                <p class="font-[600] text-[#111111]">Free</p>
                                <ul class="mt-3 space-y-1.5 text-[#6b7280]">
                                    <li v-for="(f, i) in comparisonPlan.features" :key="i">· {{ f }}</li>
                                </ul>
                                <p v-if="comparisonPlan.limits?.users" class="mt-3 text-[12px] text-[#898989]">
                                    Max {{ comparisonPlan.limits.users }} users · {{ comparisonPlan.limits.pages }} pages
                                </p>
                            </div>
                            <div class="rounded-[8px] border border-[#111111] p-4">
                                <p class="font-[600] text-[#111111]">Pro</p>
                                <ul class="mt-3 space-y-1.5 text-[#374151]">
                                    <li v-for="(f, i) in targetPlan.features" :key="i">· {{ f }}</li>
                                </ul>
                                <p class="mt-3 text-[12px] text-[#898989]">Unlimited users & pages</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[12px] border border-dashed border-[#e5e7eb] p-5">
                        <h3 class="text-[14px] font-[600] text-[#111111]">Included on all plans</h3>
                        <ul class="mt-2 grid gap-1 sm:grid-cols-2 text-[13px] text-[#6b7280]">
                            <li v-for="item in includedOnAllPlans" :key="item">· {{ item }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </template>
    </AuthenticatedLayout>
</template>
