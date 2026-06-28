import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';
import { usePlanFeatures } from '@/Composables/usePlanFeatures';

export const sidebarNav = [
    {
        name: 'Dashboard',
        route: 'dashboard',
        activeRoutes: ['dashboard'],
        pathPrefix: '/dashboard',
        permission: 'dashboard.view',
    },
    {
        name: 'Inbox',
        route: 'inbox.index',
        activeRoutes: ['inbox.*'],
        pathPrefix: '/inbox',
        permission: 'inbox.view',
    },
    {
        name: 'Books',
        route: 'books.index',
        activeRoutes: ['books.*'],
        pathPrefix: '/books',
        permission: 'books.view',
        planFeature: 'books',
    },
    {
        name: 'Wiki',
        route: 'wiki.index',
        activeRoutes: ['wiki.*'],
        pathPrefix: '/wiki',
        permission: 'wiki.view',
    },
    {
        name: 'Snippets',
        route: 'snippets.index',
        activeRoutes: ['snippets.*'],
        pathPrefix: '/snippets',
        permission: 'snippets.view',
    },
    {
        name: 'SOPs',
        route: 'sops.index',
        activeRoutes: ['sops.*'],
        pathPrefix: '/sops',
        permission: 'sops.view',
        planFeature: 'sops',
    },
    {
        name: 'Troubleshooting',
        route: 'troubleshooting.index',
        activeRoutes: ['troubleshooting.*'],
        pathPrefix: '/troubleshooting',
        permission: 'troubleshooting.view',
        planFeature: 'troubleshooting',
    },
    {
        name: 'Projects',
        route: 'projects.index',
        activeRoutes: ['projects.*'],
        pathPrefix: '/projects',
        permission: 'projects.view',
        planFeature: 'projects',
    },
    {
        name: 'Knowledge',
        route: 'knowledge.index',
        activeRoutes: ['knowledge.*'],
        pathPrefix: '/knowledge-graph',
        permission: 'knowledge.view',
    },
    {
        name: 'Assistant',
        route: 'assistant.index',
        activeRoutes: ['assistant.*'],
        pathPrefix: '/assistant',
        permission: 'assistant.use',
        planFeature: 'assistant',
    },
    {
        name: 'Tools',
        route: 'tools.index',
        activeRoutes: ['tools.*'],
        pathPrefix: '/tools',
        permission: 'tools.view',
        planFeature: 'tools',
    },
    {
        name: 'Assets',
        route: 'assets.index',
        activeRoutes: ['assets.*'],
        pathPrefix: '/assets',
        permission: 'assets.view',
    },
    {
        name: 'Settings',
        route: 'settings.index',
        activeRoutes: ['settings.*', 'profile.*'],
        pathPrefix: '/settings',
        permission: 'settings.view',
    },
];

export function useSidebarNav() {
    const page = usePage();
    const { can } = usePermissions();
    const { hasFeature, requiredPlanForFeature, requiredPlanLabel, upgradeUrl } = usePlanFeatures();

    const visibleNav = computed(() => sidebarNav
        .filter((item) => can(item.permission))
        .map((item) => {
            if (!item.planFeature) {
                return { ...item, locked: false, requiredPlan: null };
            }

            const locked = !hasFeature(item.planFeature);

            return {
                ...item,
                locked,
                requiredPlan: locked ? requiredPlanForFeature(item.planFeature) : null,
            };
        }));

    const currentPath = computed(() => {
        const url = page.url ?? '/';
        const [path] = url.split('?');

        return path;
    });

    const isNavActive = (item) => {
        if (item.activeRoutes.some((pattern) => route().current(pattern))) {
            return true;
        }

        if (item.pathPrefix && (currentPath.value === item.pathPrefix || currentPath.value.startsWith(`${item.pathPrefix}/`))) {
            return true;
        }

        if (item.route === 'settings.index' && currentPath.value.startsWith('/profile')) {
            return true;
        }

        return false;
    };

    return { sidebarNav: visibleNav, isNavActive, requiredPlanLabel, upgradeUrl };
}
