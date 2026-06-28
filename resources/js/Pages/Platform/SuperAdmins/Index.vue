<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import PlatformNav from '@/Components/PlatformNav.vue';
import { confirmDelete } from '@/Composables/useConfirm';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ superAdmins: Array, candidates: Array, filters: Object });

const q = ref(props.filters?.q ?? '');

const search = () => {
    router.get(route('platform.super-admins.index'), { q: q.value || undefined }, { preserveState: true });
};

const promote = (userId) => {
    router.post(route('platform.super-admins.store'), { user_id: userId }, { preserveScroll: true });
};

const revoke = async (user) => {
    if (await confirmDelete(`Remove super admin access for ${user.name}?`, 'Revoke super admin?')) {
        router.delete(route('platform.super-admins.destroy', user.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="Platform Super Admins" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Platform · Super Admins</h1>
        </template>

        <PlatformNav />

        <div class="mb-6 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="text-[16px] font-[600] text-[#111111]">Promote user</h2>
            <p class="mt-1 text-[13px] text-[#6b7280]">Search by name or email, then promote to platform super admin.</p>
            <form @submit.prevent="search" class="mt-4 flex gap-2">
                <input v-model="q" placeholder="Search users..." class="flex-1 rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[14px] text-[#111111] placeholder:text-[#898989]" />
                <button type="submit" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424]">Search</button>
            </form>
            <div v-if="candidates.length" class="mt-4 space-y-2">
                <div v-for="user in candidates" :key="user.id" class="flex items-center justify-between rounded-[8px] border border-[#f3f4f6] px-4 py-3">
                    <div>
                        <p class="text-[14px] font-[500] text-[#111111]">{{ user.name }}</p>
                        <p class="text-[13px] text-[#898989]">{{ user.email }}</p>
                    </div>
                    <button type="button" class="rounded-[8px] border border-[#e5e7eb] px-3 py-1.5 text-[13px] font-[500] hover:bg-[#f8f9fa]" @click="promote(user.id)">Promote</button>
                </div>
            </div>
        </div>

        <div class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <h2 class="mb-4 text-[16px] font-[600] text-[#111111]">Current super admins</h2>
            <div class="space-y-2">
                <div v-for="user in superAdmins" :key="user.id" class="flex items-center justify-between rounded-[8px] border border-[#f3f4f6] px-4 py-3">
                    <div>
                        <p class="text-[14px] font-[500] text-[#111111]">{{ user.name }}</p>
                        <p class="text-[13px] text-[#898989]">{{ user.email }} · {{ user.role?.name ?? 'No role' }}</p>
                    </div>
                    <button
                        v-if="user.id !== $page.props.auth.user.id"
                        type="button"
                        class="rounded-[8px] border border-[#fecaca] px-3 py-1.5 text-[13px] font-[500] text-[#ef4444] hover:bg-[#fef2f2]"
                        @click="revoke(user)"
                    >Revoke</button>
                    <span v-else class="text-[12px] text-[#898989]">You</span>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
