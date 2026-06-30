<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    linkProject: { type: Object, default: null },
});
</script>

<template>
    <div
        v-if="linkProject"
        class="rounded-[12px] border border-[#dbeafe] bg-[#eff6ff] px-4 py-3 text-[13px] text-[#1e40af]"
    >
        Dokumentasi ini akan otomatis ditautkan ke proyek
        <Link :href="linkProject.url" class="font-[600] underline hover:text-[#1d4ed8]">{{ linkProject.name }}</Link>.
    </div>
</template>
