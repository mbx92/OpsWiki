<script setup>
import { computed } from 'vue';

const props = defineProps({
    platform: { type: String, required: true },
});

const labels = {
    macos: 'macOS',
    sql: 'SQL Query',
    postgresql: 'PostgreSQL',
};

const label = computed(() => labels[props.platform] ?? props.platform);
</script>

<template>
    <span class="inline-flex shrink-0 items-center rounded-[9999px] bg-[#eef2ff] px-2.5 py-0.5 text-[12px] font-[500] text-[#4338ca]">
        {{ label }}
    </span>
</template>
