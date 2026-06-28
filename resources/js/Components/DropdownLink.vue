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
        class="block w-full px-4 py-2.5 text-left text-[14px] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111] focus:bg-[#f8f9fa] focus:outline-none"
    >
        <slot />
    </Link>
</template>
