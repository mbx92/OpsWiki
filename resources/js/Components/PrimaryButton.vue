<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
    form: {
        type: String,
        default: undefined,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :type="type"
        :form="form"
        :disabled="disabled"
        class="inline-flex h-10 items-center justify-center rounded-[8px] bg-[#111111] px-[20px] text-[14px] font-[600] leading-none text-white transition-colors hover:bg-[#242424] disabled:cursor-not-allowed disabled:bg-[#e5e7eb] disabled:text-[#6b7280]"
    >
        <slot />
    </button>
</template>
