<script setup>
import { toast, useToasts } from '@/Composables/useToast';

const { toasts } = useToasts();

const styles = {
    success: 'border-[#bbf7d0] bg-[#f0fdf4] text-[#16a34a]',
    error: 'border-[#fecaca] bg-[#fef2f2] text-[#ef4444]',
    info: 'border-[#e5e7eb] bg-white text-[#111111]',
};
</script>

<template>
    <div class="pointer-events-none fixed bottom-4 right-4 z-[110] flex w-full max-w-sm flex-col gap-2 px-4 sm:px-0">
        <TransitionGroup
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-2 opacity-0"
            enter-to-class="translate-y-0 opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="translate-y-0 opacity-100"
            leave-to-class="translate-y-2 opacity-0"
        >
            <div
                v-for="item in toasts"
                :key="item.id"
                class="pointer-events-auto flex items-start justify-between gap-3 rounded-[10px] border px-4 py-3 text-[14px] font-[500] shadow-[0_4px_16px_rgba(0,0,0,0.08)]"
                :class="styles[item.type] ?? styles.info"
            >
                <span class="leading-snug">{{ item.message }}</span>
                <button
                    type="button"
                    class="shrink-0 text-[18px] leading-none opacity-60 hover:opacity-100"
                    @click="toast.remove(item.id)"
                >
                    ×
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>
