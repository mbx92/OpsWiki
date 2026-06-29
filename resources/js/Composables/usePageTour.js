import { runTour } from '@/tours/driverConfig';
import { resolvePageTour } from '@/tours/pageTours';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePageTour() {
    const page = usePage();

    const currentTour = computed(() => {
        const routeName = route().current() ?? undefined;

        return resolvePageTour(routeName);
    });

    const hasPageTour = computed(() => currentTour.value.steps.length > 0);

    const startPageTour = () => {
        runTour(currentTour.value.steps);
    };

    return {
        currentTour,
        hasPageTour,
        startPageTour,
    };
}
