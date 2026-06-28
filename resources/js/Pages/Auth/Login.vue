<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign in" />

        <h1 style="font-family: 'Manrope', 'Inter', sans-serif; font-weight: 700; font-size: 22px; letter-spacing: -0.5px; color: #111111;" class="mb-1">Sign in to Wiki</h1>
        <p class="mb-6 text-[14px] text-[#6b7280]">Welcome back. Enter your credentials to continue.</p>

        <div v-if="status" class="mb-4 rounded-[8px] bg-[#f0fdf4] border border-[#bbf7d0] px-4 py-3 text-[14px] font-[500] text-[#16a34a]">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1.5 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="you@company.com"
                />
                <InputError class="mt-1.5" :message="form.errors.email" />
            </div>

            <div>
                <div class="flex items-center justify-between">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[13px] text-[#6b7280] underline-offset-2 hover:text-[#111111] hover:underline"
                    >
                        Forgot password?
                    </Link>
                </div>
                <TextInput
                    id="password"
                    type="password"
                    class="mt-1.5 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-1.5" :message="form.errors.password" />
            </div>

            <div>
                <label class="flex items-center gap-2">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="text-[14px] text-[#374151]">Remember me</span>
                </label>
            </div>

            <PrimaryButton
                type="submit"
                class="mt-2 w-full justify-center"
                :class="{ 'opacity-50': form.processing }"
                :disabled="form.processing"
            >
                Sign in
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
