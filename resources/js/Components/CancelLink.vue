<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Link
        :href="href"
        class="inline-flex h-10 items-center justify-center rounded-[8px] border border-[#e5e7eb] bg-white px-[20px] text-[14px] font-[600] leading-none text-[#111111] transition-colors hover:bg-[#f8f9fa]"
    >
        <slot>Cancel</slot>
    </Link>
</template>
