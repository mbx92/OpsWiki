<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex h-10 items-center justify-center rounded-[8px] border border-[#e5e7eb] bg-white px-[20px] text-[14px] font-[600] leading-none text-[#111111] transition-colors hover:bg-[#f8f9fa] disabled:cursor-not-allowed disabled:opacity-50"
    >
        <slot />
    </button>
</template>
