import { driver } from 'driver.js';

export function filterExistingSteps(steps) {
    return steps.filter((step) => {
        if (!step.element) {
            return true;
        }

        return Boolean(document.querySelector(step.element));
    });
}

export function runTour(steps, { onComplete } = {}) {
    const driverObj = driver({
        showProgress: true,
        progressText: '{{current}} / {{total}}',
        nextBtnText: 'Lanjut',
        prevBtnText: 'Kembali',
        doneBtnText: 'Selesai',
        allowClose: true,
        overlayOpacity: 0.55,
        stagePadding: 8,
        stageRadius: 12,
        popoverClass: 'opswiki-tour-popover',
        steps: filterExistingSteps(steps),
        onDestroyed: () => {
            onComplete?.();
        },
    });

    driverObj.drive();

    return driverObj;
}
