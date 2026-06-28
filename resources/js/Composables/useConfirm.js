import { reactive } from 'vue';

export const confirmState = reactive({
    open: false,
    title: '',
    message: '',
    confirmText: 'Confirm',
    cancelText: 'Cancel',
    variant: 'default',
    resolve: null,
});

export function confirm(options = {}) {
    return new Promise((resolve) => {
        confirmState.title = options.title ?? 'Are you sure?';
        confirmState.message = options.message ?? '';
        confirmState.confirmText = options.confirmText ?? (options.variant === 'danger' ? 'Delete' : 'Confirm');
        confirmState.cancelText = options.cancelText ?? 'Cancel';
        confirmState.variant = options.variant ?? 'default';
        confirmState.resolve = resolve;
        confirmState.open = true;
    });
}

export function confirmDelete(message, title = 'Delete item?') {
    return confirm({
        title,
        message,
        variant: 'danger',
    });
}

export function resolveConfirm(value) {
    confirmState.resolve?.(value);
    confirmState.open = false;
    confirmState.resolve = null;
}

export function useConfirmState() {
    return confirmState;
}
