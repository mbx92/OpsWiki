import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePlanFeatures() {
    const page = usePage();

    const plan = computed(() => page.props.plan ?? null);

    const hasFeature = (feature) => {
        if (plan.value?.god_mode) {
            return true;
        }

        const features = plan.value?.features ?? [];

        return features.includes(feature);
    };

    const limit = (key) => {
        if (plan.value?.god_mode) {
            return null;
        }

        return plan.value?.limits?.[key] ?? null;
    };

    const usage = (key) => plan.value?.usage?.[key] ?? 0;

    const atLimit = (key) => {
        const max = limit(key);

        if (max === null || max === undefined) {
            return false;
        }

        return usage(key) >= max;
    };

    const requiredPlanLabel = (slug) => {
        if (!slug) {
            return 'Pro';
        }

        return slug.charAt(0).toUpperCase() + slug.slice(1);
    };

    const requiredPlanForFeature = (feature) => {
        const plans = page.props.saas?.featurePlans ?? {};

        return plans[feature] ?? 'pro';
    };

    const upgradeUrl = (planSlug = 'pro', feature = null) => {
        const routeName = planSlug === 'team' ? 'upgrade.team' : 'upgrade.pro';

        if (feature) {
            return route(routeName, { feature });
        }

        return route(routeName);
    };

    return { plan, hasFeature, limit, usage, atLimit, requiredPlanLabel, requiredPlanForFeature, upgradeUrl };
}
