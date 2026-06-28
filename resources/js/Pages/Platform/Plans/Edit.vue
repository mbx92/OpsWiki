<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import InputLabel from '@/Components/InputLabel.vue';
import FormHeaderActions from '@/Components/FormHeaderActions.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    plan: Object,
    gateOptions: { type: Array, default: () => [] },
    marketingOptions: { type: Array, default: () => [] },
    includedOnAllPlans: { type: Array, default: () => [] },
    defaultMarketingFeatures: { type: Array, default: () => [] },
    defaultGateKeys: { type: Array, default: () => [] },
});

const featureInput = ref('');
const form = useForm({
    name: props.plan.name,
    description: props.plan.description ?? '',
    price_monthly_cents: props.plan.price_monthly_cents,
    price_yearly_cents: props.plan.price_yearly_cents,
    currency: props.plan.currency,
    features: [...(props.plan.features ?? [])],
    gates: [...(props.plan.gates ?? props.defaultGateKeys)],
    limit_users: props.plan.limits?.users ?? '',
    limit_pages: props.plan.limits?.pages ?? '',
    is_active: props.plan.is_active,
    is_popular: props.plan.is_popular,
    sort_order: props.plan.sort_order,
});

const submit = () => form.put(route('platform.plans.update', props.plan.id));

const addFeature = () => {
    const value = featureInput.value.trim();
    if (value && !form.features.includes(value)) {
        form.features.push(value);
        featureInput.value = '';
    }
};

const removeFeature = (index) => form.features.splice(index, 1);

const resetMarketingFeatures = () => {
    form.features = [...props.defaultMarketingFeatures];
};

const resetGateDefaults = () => {
    form.gates = [...props.defaultGateKeys];
};

const customFeatures = () => form.features.filter((feature) => !props.marketingOptions.includes(feature));
</script>

<template>
    <Head :title="`Edit plan: ${plan.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('platform.plans.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Plans</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Edit {{ plan.name }}</h1>
                </div>
                <FormHeaderActions form-id="plan-form" :cancel-href="route('platform.plans.index')" save-label="Save" :processing="form.processing" />
            </div>
        </template>

        <PlatformNav />

        <form id="plan-form" @submit.prevent="submit" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div><InputLabel value="Name" /><input v-model="form.name" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" required /></div>
                <div><InputLabel value="Currency" /><input v-model="form.currency" maxlength="3" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Monthly price (cents)" /><input v-model.number="form.price_monthly_cents" type="number" min="0" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Yearly price (cents)" /><input v-model.number="form.price_yearly_cents" type="number" min="0" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Max users (empty = unlimited)" /><input v-model="form.limit_users" type="number" min="0" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" /></div>
                <div><InputLabel value="Max pages (empty = unlimited)" /><input v-model="form.limit_pages" type="number" min="0" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" /></div>
            </div>
            <div><InputLabel value="Description" /><textarea v-model="form.description" rows="2" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" /></div>

            <div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <InputLabel value="Feature gates (enforced in app)" />
                    <button type="button" @click="resetGateDefaults" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[12px] text-[#6b7280]">Reset tier defaults</button>
                </div>
                <p class="mt-1 text-[12px] text-[#6b7280]">Checked gates are unlocked for workspaces on this plan. Unchecked gates block routes, sidebar items, and limits.</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    <label
                        v-for="gate in gateOptions"
                        :key="gate.key"
                        class="flex items-start gap-3 rounded-[8px] border border-[#f3f4f6] px-3 py-2.5 hover:bg-[#f8f9fa]"
                    >
                        <input
                            v-model="form.gates"
                            type="checkbox"
                            :value="gate.key"
                            class="mt-0.5 rounded border-[#e5e7eb]"
                        />
                        <span class="min-w-0">
                            <span class="block text-[13px] font-[500] text-[#111111]">{{ gate.label }}</span>
                            <span class="block text-[11px] text-[#6b7280]">{{ gate.description }}</span>
                            <span class="mt-1 inline-block text-[10px] uppercase tracking-wide text-[#898989]">Default: {{ gate.min_plan }}+</span>
                        </span>
                    </label>
                </div>
            </div>

            <div>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <InputLabel value="Marketing features (pricing page)" />
                    <button type="button" @click="resetMarketingFeatures" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[12px] text-[#6b7280]">Reset defaults</button>
                </div>
                <p class="mt-1 text-[12px] text-[#6b7280]">Display-only bullets on the landing page. Does not control access — use feature gates above.</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    <label
                        v-for="option in marketingOptions"
                        :key="option"
                        class="flex items-center gap-3 rounded-[8px] border border-[#f3f4f6] px-3 py-2 hover:bg-[#f8f9fa]"
                    >
                        <input
                            v-model="form.features"
                            type="checkbox"
                            :value="option"
                            class="rounded border-[#e5e7eb]"
                        />
                        <span class="text-[13px] text-[#374151]">{{ option }}</span>
                    </label>
                </div>
                <div class="mt-4">
                    <p class="text-[12px] font-[500] text-[#6b7280]">Custom bullets</p>
                    <div class="mt-1.5 flex gap-2">
                        <input v-model="featureInput" @keydown.enter.prevent="addFeature" class="flex-1 rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px]" placeholder="Add custom feature..." />
                        <button type="button" @click="addFeature" class="rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[13px]">Add</button>
                    </div>
                    <ul v-if="customFeatures().length" class="mt-2 space-y-1 text-[13px] text-[#374151]">
                        <li v-for="(feature, index) in customFeatures()" :key="feature" class="flex items-center justify-between rounded-[6px] px-2 py-1 hover:bg-[#f8f9fa]">
                            <span>• {{ feature }}</span>
                            <button type="button" class="text-[12px] text-[#ef4444]" @click="removeFeature(form.features.indexOf(feature))">Remove</button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="rounded-[8px] border border-dashed border-[#e5e7eb] p-4">
                <h3 class="text-[14px] font-[600] text-[#111111]">Included on all plans</h3>
                <p class="mt-1 text-[12px] text-[#6b7280]">Always available (subject to workspace RBAC, not plan-gated).</p>
                <ul class="mt-2 list-inside list-disc space-y-1 text-[13px] text-[#374151]">
                    <li v-for="item in includedOnAllPlans" :key="item">{{ item }}</li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-4 text-[14px]">
                <label class="flex items-center gap-2"><input v-model="form.is_active" type="checkbox" class="rounded border-[#e5e7eb]" /> Active</label>
                <label class="flex items-center gap-2"><input v-model="form.is_popular" type="checkbox" class="rounded border-[#e5e7eb]" /> Popular badge</label>
            </div>
        </form>
    </AuthenticatedLayout>
</template>
