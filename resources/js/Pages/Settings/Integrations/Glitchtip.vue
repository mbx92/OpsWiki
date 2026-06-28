<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    glitchtip: Object,
});

const form = useForm({
    enabled: props.glitchtip.enabled,
    dsn: '',
    environment: props.glitchtip.environment,
    traces_sample_rate: props.glitchtip.traces_sample_rate,
    send_default_pii: props.glitchtip.send_default_pii,
});

const save = () => form.put(route('settings.integrations.glitchtip.update'));

const testConnection = () => router.post(route('settings.integrations.glitchtip.test'));
</script>

<template>
    <Head title="GlitchTip Integration" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.integrations')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Integrations</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">GlitchTip</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <form @submit.prevent="save" class="space-y-6 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <div>
                    <h2 class="text-[16px] font-[600] text-[#111111]">Error tracking</h2>
                    <p class="mt-1 text-[13px] text-[#6b7280]">
                        Send application errors and exceptions to GlitchTip (Sentry-compatible).
                    </p>
                </div>

                <label class="flex items-center gap-3">
                    <input v-model="form.enabled" type="checkbox" class="rounded border-[#e5e7eb]" />
                    <span class="text-[14px] text-[#111111]">Enable GlitchTip reporting</span>
                </label>

                <div>
                    <InputLabel value="DSN" />
                    <input
                        v-model="form.dsn"
                        type="password"
                        autocomplete="off"
                        :placeholder="glitchtip.has_dsn ? glitchtip.dsn_hint || 'Leave blank to keep current DSN' : 'https://key@glitchtip.example.com/1'"
                        class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 font-mono text-[13px]"
                    />
                    <p class="mt-1 text-[12px] text-[#898989]">
                        From GlitchTip: Project → Settings → Client Keys (DSN)
                    </p>
                    <InputError :message="form.errors.dsn" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <InputLabel value="Environment" />
                        <input
                            v-model="form.environment"
                            type="text"
                            placeholder="production"
                            class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]"
                        />
                        <InputError :message="form.errors.environment" />
                    </div>
                    <div>
                        <InputLabel value="Traces sample rate" />
                        <input
                            v-model.number="form.traces_sample_rate"
                            type="number"
                            min="0"
                            max="1"
                            step="0.1"
                            class="mt-1 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]"
                        />
                        <p class="mt-1 text-[12px] text-[#898989]">0 = off, 1 = all requests (performance)</p>
                        <InputError :message="form.errors.traces_sample_rate" />
                    </div>
                </div>

                <label class="flex items-center gap-3">
                    <input v-model="form.send_default_pii" type="checkbox" class="rounded border-[#e5e7eb]" />
                    <span class="text-[14px] text-[#111111]">Send default PII (user IP, cookies)</span>
                </label>

                <div class="flex flex-wrap gap-3 border-t border-[#e5e7eb] pt-6">
                    <button type="submit" :disabled="form.processing" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50">
                        Save settings
                    </button>
                    <button type="button" @click="testConnection" class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]">
                        Send test event
                    </button>
                </div>
            </form>

            <aside class="mt-6 rounded-[12px] border border-[#e5e7eb] bg-white p-5">
                <h2 class="text-[14px] font-[600] text-[#111111]">Setup notes</h2>
                <ul class="mt-3 space-y-2 text-[13px] text-[#6b7280]">
                    <li>GlitchTip uses the Sentry SDK — paste your project DSN as-is.</li>
                    <li>After saving, unhandled exceptions are reported on the next request.</li>
                    <li>Use <strong>Send test event</strong> to verify the DSN without triggering an error.</li>
                    <li>You can also set <code class="rounded bg-[#f5f5f5] px-1 text-[12px]">SENTRY_LARAVEL_DSN</code> in <code class="rounded bg-[#f5f5f5] px-1 text-[12px]">.env</code> as fallback.</li>
                </ul>
            </aside>
        </div>
    </AuthenticatedLayout>
</template>
