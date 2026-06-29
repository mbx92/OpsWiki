<script setup>
import { useProductTour } from '@/Composables/useProductTour';
import { router, usePage } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref, watch } from 'vue';

const page = usePage();
const { startTour, restartTour, hasCompletedTour } = useProductTour();
const autoStartAttempted = ref(false);

const TOUR_EVENT = 'opswiki:start-tour';

function shouldAutoStart() {
    if (autoStartAttempted.value || hasCompletedTour()) {
        return false;
    }

    const path = page.url?.split('?')[0] ?? '';

    return path === '/dashboard' || path.endsWith('/dashboard');
}

function scheduleAutoStart() {
    if (!shouldAutoStart()) {
        return;
    }

    autoStartAttempted.value = true;

    window.setTimeout(() => startTour({ force: true }), 600);
}

function onTourEvent(event) {
    if (event.detail?.restart) {
        restartTour();
        return;
    }

    startTour({ force: true });
}

onMounted(() => {
    window.addEventListener(TOUR_EVENT, onTourEvent);
    scheduleAutoStart();
});

onUnmounted(() => {
    window.removeEventListener(TOUR_EVENT, onTourEvent);
});

watch(
    () => page.url,
    () => {
        if (!autoStartAttempted.value) {
            scheduleAutoStart();
        }
    },
);

router.on('navigate', () => {
    if (!autoStartAttempted.value) {
        scheduleAutoStart();
    }
});
</script>

<template>
    <span class="hidden" aria-hidden="true" />
</template>
