<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ user: Object, roles: Array });

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role_id: props.user.role_id,
    is_active: props.user.is_active,
});
</script>

<template>
    <Head :title="`Edit: ${user.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.users.index')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Users</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">Edit User</h1>
            </div>
        </template>

        <form @submit.prevent="form.put(route('settings.users.update', user.id))" class="max-w-xl space-y-4 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
            <div>
                <InputLabel value="Name" />
                <input v-model="form.name" required class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                <InputError :message="form.errors.name" />
            </div>
            <div>
                <InputLabel value="Email" />
                <input v-model="form.email" type="email" required class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                <InputError :message="form.errors.email" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel value="New password" />
                    <input v-model="form.password" type="password" placeholder="Leave blank to keep" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                    <InputError :message="form.errors.password" />
                </div>
                <div>
                    <InputLabel value="Confirm password" />
                    <input v-model="form.password_confirmation" type="password" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>
            </div>
            <div>
                <InputLabel value="Role" />
                <select v-model="form.role_id" required class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                </select>
                <InputError :message="form.errors.role_id" />
            </div>
            <label class="flex items-center gap-3">
                <input v-model="form.is_active" type="checkbox" class="rounded border-[#e5e7eb]" />
                <span class="text-[14px] text-[#111111]">Active account</span>
            </label>
            <button type="submit" :disabled="form.processing" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50">
                Save changes
            </button>
        </form>
    </AuthenticatedLayout>
</template>
