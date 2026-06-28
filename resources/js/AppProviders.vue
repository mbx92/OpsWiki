<script setup>
import ConfirmDialog from '@/Components/ConfirmDialog.vue';
import ToastContainer from '@/Components/ToastContainer.vue';
import { toast } from '@/Composables/useToast';
import { router, usePage } from '@inertiajs/vue3';
import { onMounted } from 'vue';

function showFlash() {
    const flash = usePage().props.flash;

    if (flash?.success) {
        toast.success(flash.success);
    }

    if (flash?.error) {
        toast.error(flash.error);
    }
}

onMounted(showFlash);

router.on('success', showFlash);
</script>

<template>
    <ToastContainer />
    <ConfirmDialog />
</template>
