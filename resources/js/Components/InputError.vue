<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-[13px] font-[500] text-[#ef4444]">
            {{ message }}
        </p>
    </div>
</template>
