<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="block h-10 w-full rounded-[8px] border border-[#e5e7eb] bg-white px-[14px] text-[15px] text-[#111111] outline-none placeholder:text-[#898989] focus:border-[#111111] focus:ring-0 disabled:bg-[#f8f9fa] disabled:text-[#898989]"
        v-model="model"
        ref="input"
    />
</template>
