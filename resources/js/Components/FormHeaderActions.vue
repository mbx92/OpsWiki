<script setup>
import CancelLink from '@/Components/CancelLink.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    formId: {
        type: String,
        required: true,
    },
    cancelHref: {
        type: String,
        required: true,
    },
    saveLabel: {
        type: String,
        default: 'Save',
    },
    cancelLabel: {
        type: String,
        default: 'Cancel',
    },
    processing: {
        type: Boolean,
        default: false,
    },
    saveDisabled: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <CancelLink :href="cancelHref">{{ cancelLabel }}</CancelLink>
        <PrimaryButton
            type="submit"
            :form="formId"
            :disabled="processing || saveDisabled"
        >
            {{ saveLabel }}
        </PrimaryButton>
    </div>
</template>
