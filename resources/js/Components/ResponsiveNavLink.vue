<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
        ? 'block w-full rounded-[8px] px-3 py-2 text-[14px] font-[500] text-[#111111] bg-[#f5f5f5]'
        : 'block w-full rounded-[8px] px-3 py-2 text-[14px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] hover:text-[#111111]',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
