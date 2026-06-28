import { reactive } from 'vue';

let nextId = 0;

export const toasts = reactive([]);

function remove(id) {
    const index = toasts.findIndex((t) => t.id === id);

    if (index >= 0) {
        toasts.splice(index, 1);
    }
}

function push(message, type = 'info', duration = 4000) {
    const id = ++nextId;

    toasts.push({ id, message, type });

    if (duration > 0) {
        setTimeout(() => remove(id), duration);
    }

    return id;
}

export const toast = {
    success: (message, duration) => push(message, 'success', duration ?? 4000),
    error: (message, duration) => push(message, 'error', duration ?? 5000),
    info: (message, duration) => push(message, 'info', duration ?? 4000),
    remove,
};

export function useToasts() {
    return { toasts, toast };
}
