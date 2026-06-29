<script setup>
import { usePageTour } from '@/Composables/usePageTour';

const { currentTour, hasPageTour, startPageTour } = usePageTour();
</script>

<template>
    <button
        v-if="hasPageTour"
        type="button"
        data-page-tour="page-help"
        class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-[#e5e7eb] bg-white text-[14px] font-[700] text-[#6b7280] transition-colors hover:border-[#111111] hover:bg-[#f8f9fa] hover:text-[#111111] focus:outline-none focus:ring-2 focus:ring-[#111111]/20"
        :title="`Panduan ${currentTour.label}`"
        :aria-label="`Panduan ${currentTour.label}`"
        @click="startPageTour"
    >
        ?
    </button>
</template>
