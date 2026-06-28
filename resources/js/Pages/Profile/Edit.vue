<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import CancelLink from '@/Components/CancelLink.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">
                        Profile
                    </h1>
                </div>
                <CancelLink :href="route('settings.index')" />
            </div>
        </template>

        <div class="mx-auto max-w-2xl space-y-6">
            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 sm:p-8">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                />
            </div>

            <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6 sm:p-8">
                <UpdatePasswordForm />
            </div>

            <div class="rounded-[12px] border border-[#fecaca] bg-white p-6 sm:p-8">
                <DeleteUserForm />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
