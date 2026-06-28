<script setup>
defineProps({
    name: { type: String, required: true },
    color: { type: String, default: null },
});
</script>

<template>
    <span class="inline-flex items-center rounded-[9999px] bg-[#f5f5f5] px-2.5 py-0.5 text-[12px] font-[500] text-[#374151]">
        {{ name }}
    </span>
</template>
