<script setup>
import { useCopy } from '@/Composables/useCopy';
import { ref } from 'vue';

const props = defineProps({
    text: { type: String, required: true },
    label: { type: String, default: 'Copy' },
});

const { copy } = useCopy();
const copied = ref(false);

const handleCopy = async () => {
    await copy(props.text);
    copied.value = true;
    setTimeout(() => (copied.value = false), 2000);
};
</script>

<template>
    <button
        type="button"
        @click="handleCopy"
        class="inline-flex items-center gap-1.5 rounded-[6px] border border-[#e5e7eb] bg-white px-2.5 py-1 text-[12px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa]"
    >
        <svg v-if="!copied" class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
        </svg>
        <svg v-else class="h-3.5 w-3.5 text-[#10b981]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        {{ copied ? 'Copied!' : label }}
    </button>
</template>
