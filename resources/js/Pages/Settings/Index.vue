<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePermissions } from '@/Composables/usePermissions';
import { usePlanFeatures } from '@/Composables/usePlanFeatures';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const { can } = usePermissions();
const { hasFeature, requiredPlanLabel, upgradeUrl } = usePlanFeatures();

const settingsCards = computed(() => [
    {
        title: 'Profile',
        description: 'Update name, email, and password.',
        href: route('profile.edit'),
        show: true,
        locked: false,
    },
    {
        title: 'Workspace',
        description: 'Domain, subdomain, and subscription plan.',
        href: route('settings.workspace'),
        show: can('settings.view'),
        locked: false,
    },
    {
        title: 'Billing',
        description: 'Subscription, invoices, and payment history.',
        href: route('settings.billing'),
        show: can('settings.view'),
        locked: false,
    },
    {
        title: 'Categories',
        description: 'Manage wiki and snippet categories.',
        href: route('settings.categories'),
        show: can('settings.categories'),
        locked: false,
    },
    {
        title: 'Tags',
        description: 'Manage tags for pages and snippets.',
        href: route('settings.tags'),
        show: can('settings.tags'),
        locked: false,
    },
    {
        title: 'Users',
        description: 'Manage accounts, roles, and access.',
        href: route('settings.users.index'),
        show: can('users.manage'),
        locked: !hasFeature('users.manage'),
        requiredPlan: 'pro',
    },
    {
        title: 'Roles',
        description: 'Configure role permissions.',
        href: route('settings.roles.index'),
        show: can('roles.view'),
        locked: false,
    },
    {
        title: 'Integrations',
        description: 'Connect MinIO, GlitchTip, and other services.',
        href: route('settings.integrations'),
        show: can('settings.integrations'),
        locked: !hasFeature('integrations'),
        requiredPlan: 'pro',
    },
    {
        title: 'Activity Log',
        description: 'Recent create, update, delete, and export actions.',
        href: route('settings.activity'),
        show: can('activity.view'),
        locked: !hasFeature('activity'),
        requiredPlan: 'team',
    },
]);
</script>

<template>
    <Head title="Settings" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Settings</h1>
        </template>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <template v-for="card in settingsCards" :key="card.title">
                <Link
                    v-if="card.show && !card.locked"
                    :href="card.href"
                    class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 hover:bg-[#f8f9fa]"
                >
                    <h2 class="text-[16px] font-[600] text-[#111111]">{{ card.title }}</h2>
                    <p class="mt-1 text-[13px] text-[#6b7280]">{{ card.description }}</p>
                </Link>
                <Link
                    v-else-if="card.show && card.locked"
                    :href="upgradeUrl(card.requiredPlan)"
                    class="block rounded-[12px] border border-[#e5e7eb] bg-white p-6 opacity-80 hover:bg-[#f8f9fa]"
                >
                    <h2 class="text-[16px] font-[600] text-[#111111]">{{ card.title }}</h2>
                    <p class="mt-1 text-[13px] text-[#6b7280]">{{ card.description }}</p>
                    <span class="mt-3 inline-block rounded-[8px] bg-[#fef3c7] px-2.5 py-1 text-[12px] font-[500] text-[#92400e]">
                        {{ requiredPlanLabel(card.requiredPlan) }} plan required →
                    </span>
                </Link>
            </template>

            <Link v-if="$page.props.auth.user?.is_super_admin" :href="route('platform.dashboard')" class="sm:col-span-2 lg:col-span-3 block rounded-[12px] border border-[#111111] bg-[#111111] p-6 hover:bg-[#242424]">
                <h2 class="text-[16px] font-[600] text-white">Platform Admin</h2>
                <p class="mt-1 text-[13px] text-[#d1d5db]">Manage workspaces, subscriptions, pricing, policies, and super admins.</p>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
