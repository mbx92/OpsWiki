import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function usePermissions() {
    const page = usePage();

    const permissions = computed(() => page.props.auth?.user?.permissions ?? []);

    const can = (permission) => {
        if (page.props.auth?.user?.is_super_admin) {
            return true;
        }

        const perms = permissions.value;

        if (perms.includes('*')) {
            return true;
        }

        if (perms.includes(permission)) {
            return true;
        }

        return perms.some((assigned) => {
            if (!assigned.endsWith('.*')) {
                return false;
            }

            const prefix = assigned.slice(0, -2);

            return permission === prefix || permission.startsWith(`${prefix}.`);
        });
    };

    const role = computed(() => page.props.auth?.user?.role ?? null);

    return { can, role, permissions };
}
