<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { resolveConfirm, useConfirmState } from '@/Composables/useConfirm';

const state = useConfirmState();

const onConfirm = () => resolveConfirm(true);
const onCancel = () => resolveConfirm(false);
</script>

<template>
    <Modal :show="state.open" max-width="md" @close="onCancel">
        <div class="p-6">
            <h2 class="text-[16px] font-[600] text-[#111111]" style="font-family: 'Manrope', sans-serif;">
                {{ state.title }}
            </h2>
            <p v-if="state.message" class="mt-2 text-[14px] leading-relaxed text-[#6b7280]">
                {{ state.message }}
            </p>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="onCancel">{{ state.cancelText }}</SecondaryButton>
                <DangerButton v-if="state.variant === 'danger'" type="button" @click="onConfirm">
                    {{ state.confirmText }}
                </DangerButton>
                <PrimaryButton v-else type="button" @click="onConfirm">
                    {{ state.confirmText }}
                </PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
