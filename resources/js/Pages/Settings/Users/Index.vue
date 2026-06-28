<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({ users: Array, roles: Array });

const destroy = async (user) => {
    if (await confirmDelete(`Remove ${user.name} from the system?`, 'Delete user?')) {
        router.delete(route('settings.users.destroy', user.id));
    }
};
</script>

<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <Link :href="route('settings.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Settings</Link>
                    <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Users</h1>
                </div>
                <Link :href="route('settings.users.create')" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">
                    Add user
                </Link>
            </div>
        </template>

        <div class="overflow-hidden rounded-[12px] border border-[#e5e7eb] bg-white">
            <table class="min-w-full text-left text-[14px]">
                <thead class="border-b border-[#e5e7eb] bg-[#f8f9fa] text-[12px] font-[600] uppercase tracking-wider text-[#898989]">
                    <tr>
                        <th class="px-5 py-3">Name</th>
                        <th class="px-5 py-3">Email</th>
                        <th class="px-5 py-3">Role</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in users" :key="user.id" class="border-b border-[#f3f4f6] last:border-0">
                        <td class="px-5 py-4 font-[500] text-[#111111]">{{ user.name }}</td>
                        <td class="px-5 py-4 text-[#6b7280]">{{ user.email }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-[9999px] bg-[#f5f5f5] px-2.5 py-0.5 text-[12px] font-[500] text-[#374151]">
                                {{ user.role?.name ?? 'No role' }}
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <span
                                class="rounded-[9999px] px-2.5 py-0.5 text-[12px] font-[500]"
                                :class="user.is_active ? 'bg-[#ecfdf5] text-[#047857]' : 'bg-[#fef2f2] text-[#ef4444]'"
                            >
                                {{ user.is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex justify-end gap-3">
                                <Link :href="route('settings.users.edit', user.id)" class="text-[13px] text-[#374151] hover:text-[#111111]">Edit</Link>
                                <button type="button" @click="destroy(user)" class="text-[13px] text-[#ef4444]">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>
