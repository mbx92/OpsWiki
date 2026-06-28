<script setup>
defineProps({
    platform: { type: String, required: true },
});
</script>

<template>
    <span class="inline-flex shrink-0 items-center rounded-[9999px] bg-[#eef2ff] px-2.5 py-0.5 text-[12px] font-[500] capitalize text-[#4338ca]">
        {{ platform }}
    </span>
</template>
