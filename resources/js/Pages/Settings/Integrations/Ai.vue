<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ ai: Object, connected: Boolean });

const form = useForm({
    enabled: props.ai.enabled,
    base_url: props.ai.base_url,
    api_key: '',
    model: props.ai.model,
    system_prompt: props.ai.system_prompt,
});

const save = () => form.put(route('settings.integrations.ai.update'));
</script>

<template>
    <Head title="AI Assistant Integration" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('settings.integrations')" class="text-[13px] text-[#6b7280] hover:text-[#111111]">← Integrations</Link>
                <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">AI Assistant</h1>
            </div>
        </template>

        <div class="max-w-2xl">
            <div v-if="connected" class="mb-4 rounded-[12px] border border-[#bbf7d0] bg-[#f0fdf4] px-4 py-3 text-[13px] text-[#166534]">AI assistant is configured.</div>

            <form @submit.prevent="save" class="space-y-6 rounded-[12px] border border-[#e5e7eb] bg-white p-6">
                <label class="flex items-center gap-3">
                    <input v-model="form.enabled" type="checkbox" class="rounded border-[#e5e7eb]" />
                    <span class="text-[14px] text-[#111111]">Enable AI assistant</span>
                </label>

                <div>
                    <InputLabel value="API base URL" />
                    <input v-model="form.base_url" placeholder="https://api.openai.com/v1" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                    <p class="mt-1 text-[12px] text-[#898989]">OpenAI-compatible endpoint (OpenAI, Ollama, LiteLLM, etc.)</p>
                </div>

                <div>
                    <InputLabel value="API key" />
                    <input v-model="form.api_key" type="password" :placeholder="ai.has_api_key ? 'Leave blank to keep current key' : 'sk-...'" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>

                <div>
                    <InputLabel value="Model" />
                    <input v-model="form.model" placeholder="gpt-4o-mini" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                </div>

                <div>
                    <InputLabel value="Additional instructions (optional)" />
                    <textarea v-model="form.system_prompt" rows="4" placeholder="Contoh: jawab singkat, prioritaskan SOP production" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]" />
                    <p class="mt-1 text-[12px] text-[#898989]">Kebijakan keamanan wajib selalu aktif: assistant hanya boleh menjawab dari konten OpsWiki, tidak dari pengetahuan umum di luar wiki.</p>
                </div>

                <button type="submit" :disabled="form.processing" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50">Save settings</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
