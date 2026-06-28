<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps({
    roles: Array,
    permissions: Object,
    canManage: Boolean,
});

const forms = reactive({});

props.roles.forEach((role) => {
    forms[role.id] = useForm({
        permission_ids: (role.permission_ids ?? []).map((id) => Number(id)),
    });
});

const saveRole = (role) => {
    forms[role.id].put(route('settings.roles.update', role.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Roles" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Roles & Permissions</h1>
            </div>
        </template>

        <div class="space-y-6">
            <div v-for="role in roles" :key="role.id" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-[16px] font-[600] text-[#111111]">{{ role.name }}</h2>
                        <p class="mt-1 text-[13px] text-[#6b7280]">{{ role.description }}</p>
                        <p class="mt-2 text-[12px] text-[#898989]">{{ role.users_count }} user(s) · {{ role.slug }}</p>
                    </div>
                    <button
                        v-if="canManage && role.slug !== 'owner'"
                        type="button"
                        :disabled="forms[role.id]?.processing"
                        @click="saveRole(role)"
                        class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50"
                    >
                        Save permissions
                    </button>
                </div>

                <div v-if="role.slug === 'owner'" class="mt-4 rounded-[8px] bg-[#f0fdf4] border border-[#bbf7d0] px-4 py-3 text-[13px] text-[#166534]">
                    Owner has <strong>full access</strong> to every permission and cannot be modified.
                </div>

                <div v-else-if="role.slug === 'admin'" class="mt-4 mb-1 rounded-[8px] bg-[#fef3c7] border border-[#fde68a] px-4 py-3 text-[13px] text-[#92400e]">
                    Admin has limited access. Use checkboxes below — cannot assign Owner role or edit role permissions.
                </div>

                <div v-if="!canManage && role.slug !== 'owner'" class="mt-4 rounded-[8px] bg-[#f3f4f6] border border-[#e5e7eb] px-4 py-3 text-[13px] text-[#6b7280]">
                    View only. Role editing requires Owner with Team plan (or super admin).
                </div>

                <div v-if="role.slug !== 'owner'" class="mt-5 space-y-5">
                    <div v-for="(groupPermissions, group) in permissions" :key="group">
                        <h3 class="text-[12px] font-[600] uppercase tracking-wider text-[#898989]">{{ group }}</h3>
                        <div class="mt-2 grid gap-2 sm:grid-cols-2">
                            <label
                                v-for="permission in groupPermissions"
                                :key="permission.id"
                                class="flex items-center gap-3 rounded-[8px] border border-[#f3f4f6] px-3 py-2"
                                :class="!canManage ? 'opacity-70' : ''"
                            >
                                <input
                                    v-model="forms[role.id].permission_ids"
                                    type="checkbox"
                                    :value="Number(permission.id)"
                                    :disabled="!canManage || permission.slug === '*'"
                                    class="rounded border-[#e5e7eb]"
                                />
                                <span class="text-[13px] text-[#374151]">{{ permission.name }}</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
