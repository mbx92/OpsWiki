<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});
</script>

<template>
    <section>
        <header class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-[16px] font-[600] text-[#111111]" style="font-family: 'Manrope', sans-serif;">
                    Profile Information
                </h2>
                <p class="mt-1 text-[13px] text-[#6b7280]">
                    Update your account's profile information and email address.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-[13px] text-[#6b7280]">Saved.</p>
                </Transition>
                <PrimaryButton type="submit" form="profile-info-form" :disabled="form.processing">Save</PrimaryButton>
            </div>
        </header>

        <form
            id="profile-info-form"
            @submit.prevent="form.patch(route('profile.update'))"
            class="mt-6 space-y-5"
        >
            <div>
                <InputLabel for="name" value="Name" />
                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-[13px] text-[#374151]">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="font-[500] text-[#111111] underline decoration-[#d1d5db] underline-offset-2 hover:decoration-[#111111]"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-[13px] font-[500] text-[#16a34a]"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>
        </form>
    </section>
</template>
