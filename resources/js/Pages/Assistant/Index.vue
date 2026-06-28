<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, nextTick } from 'vue';

defineProps({ enabled: Boolean });

const input = ref('');
const messages = ref([]);
const loading = ref(false);
const chatRef = ref(null);

const send = async () => {
    const text = input.value.trim();
    if (!text || loading.value) return;

    messages.value.push({ role: 'user', content: text });
    input.value = '';
    loading.value = true;

    await nextTick();
    chatRef.value?.scrollTo({ top: chatRef.value.scrollHeight, behavior: 'smooth' });

    try {
        const { data } = await axios.post(route('assistant.chat'), {
            messages: messages.value,
        });

        if (data.ok) {
            messages.value.push({ role: 'assistant', content: data.reply });
        } else {
            messages.value.push({ role: 'assistant', content: data.message || 'Something went wrong.' });
        }
    } catch (e) {
        messages.value.push({ role: 'assistant', content: e.response?.data?.message || 'Request failed.' });
    } finally {
        loading.value = false;
        await nextTick();
        chatRef.value?.scrollTo({ top: chatRef.value.scrollHeight, behavior: 'smooth' });
    }
};
</script>

<template>
    <Head title="AI Assistant" />
    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-[20px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">AI Assistant</h1>
        </template>

        <div v-if="!enabled" class="rounded-[12px] border border-[#fde68a] bg-[#fffbeb] p-5 text-[14px] text-[#92400e]">
            AI assistant belum dikonfigurasi. Buka Settings → Integrations → AI Assistant.
        </div>

        <div v-else class="mb-4 rounded-[12px] border border-[#e5e7eb] bg-[#f8f9fa] px-4 py-3 text-[13px] text-[#374151]">
            Assistant hanya menjawab dari dokumentasi OpsWiki (wiki, SOP, troubleshooting, snippet, project). Pertanyaan di luar konten wiki akan ditolak.
        </div>

        <div class="mx-auto flex h-[calc(100vh-12rem)] max-w-3xl flex-col rounded-[12px] border border-[#e5e7eb] bg-white">
            <div ref="chatRef" class="flex-1 space-y-4 overflow-y-auto p-5">
                <p v-if="!messages.length" class="text-[14px] text-[#898989]">Tanya tentang dokumentasi, command, troubleshooting, SOP, atau fitur OpsWiki — hanya dari konten wiki ini.</p>
                <div
                    v-for="(msg, i) in messages"
                    :key="i"
                    class="rounded-[12px] px-4 py-3 text-[14px] leading-relaxed"
                    :class="msg.role === 'user' ? 'ml-8 bg-[#111111] text-white' : 'mr-8 bg-[#f8f9fa] text-[#374151]'"
                >
                    <p class="whitespace-pre-wrap">{{ msg.content }}</p>
                </div>
                <p v-if="loading" class="text-[13px] text-[#898989]">Thinking...</p>
            </div>
            <form @submit.prevent="send" class="flex gap-2 border-t border-[#e5e7eb] p-4">
                <input
                    v-model="input"
                    :disabled="!enabled || loading"
                    placeholder="Tanya tentang dokumentasi OpsWiki..."
                    class="flex-1 rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px] outline-none focus:border-[#111111] disabled:opacity-50"
                />
                <button type="submit" :disabled="!enabled || loading || !input.trim()" class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white disabled:opacity-50">Send</button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
