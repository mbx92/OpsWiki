<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import { useCopy } from '@/Composables/useCopy';
import { toast } from '@/Composables/useToast';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: 'Share' },
    description: { type: String, default: 'Anyone with the link can view this content without signing in.' },
    visibility: { type: String, required: true },
    publicUrl: { type: String, default: null },
    sharePath: { type: String, required: true },
    updateRoute: { type: String, required: true },
    showMakePagesPublic: { type: Boolean, default: false },
});

const emit = defineEmits(['close']);

const form = useForm({
    visibility: props.visibility,
    make_pages_public: false,
});

const linkReady = ref(props.visibility === 'public' && !!props.publicUrl);

watch(() => props.show, (open) => {
    if (open) {
        form.visibility = props.visibility;
        form.make_pages_public = false;
        linkReady.value = props.visibility === 'public' && !!props.publicUrl;
    }
});

watch(() => props.visibility, (value) => {
    if (props.show) {
        form.visibility = value;
    }
    if (value === 'public' && props.publicUrl) {
        linkReady.value = true;
    }
});

watch(() => props.publicUrl, (value) => {
    if (value && form.visibility === 'public') {
        linkReady.value = true;
    }
});

const isPublic = computed(() => form.visibility === 'public');

const activePublicUrl = computed(() => {
    if (!isPublic.value || !linkReady.value) {
        return null;
    }

    if (props.publicUrl) {
        return props.publicUrl;
    }

    if (typeof window === 'undefined') {
        return props.sharePath;
    }

    return `${window.location.origin}${props.sharePath}`;
});

const { copy } = useCopy();

const close = () => emit('close');

const save = () => {
    form.patch(props.updateRoute, {
        preserveScroll: true,
        onSuccess: () => {
            if (form.visibility === 'public') {
                linkReady.value = true;
                toast.success('Public link is ready.');
            } else {
                linkReady.value = false;
                toast.success('Sharing settings updated.');
                close();
            }
        },
    });
};

const copyLink = async () => {
    if (!activePublicUrl.value) {
        return;
    }

    await copy(activePublicUrl.value);
    toast.success('Link copied to clipboard.');
};
</script>

<template>
    <Modal :show="show" max-width="lg" @close="close">
        <div class="p-6">
            <h2 class="text-[18px] font-[700] text-[#111111]" style="font-family: 'Manrope', sans-serif;">
                {{ title }}
            </h2>
            <p class="mt-1 text-[13px] text-[#6b7280]">{{ description }}</p>

            <div class="mt-5">
                <InputLabel value="Visibility" />
                <select v-model="form.visibility" class="mt-1.5 block w-full rounded-[8px] border border-[#e5e7eb] px-3 py-2 text-[14px]">
                    <option value="private">Private — only you (logged in)</option>
                    <option value="internal">Internal — any logged-in user</option>
                    <option value="public">Public — shareable link</option>
                </select>
            </div>

            <label v-if="showMakePagesPublic && isPublic" class="mt-4 flex items-center gap-3">
                <input v-model="form.make_pages_public" type="checkbox" class="rounded border-[#e5e7eb]" />
                <span class="text-[13px] text-[#374151]">Also make all pages in this book public</span>
            </label>

            <div
                v-if="activePublicUrl"
                class="mt-5 rounded-[8px] border border-[#d1fae5] bg-[#ecfdf5] p-4"
            >
                <p class="text-[11px] font-[600] uppercase tracking-wider text-[#047857]">Share link</p>
                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <p class="min-w-0 flex-1 truncate font-mono text-[13px] text-[#065f46]" :title="activePublicUrl">
                        {{ activePublicUrl }}
                    </p>
                    <div class="flex shrink-0 gap-2">
                        <a
                            :href="activePublicUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="rounded-[8px] border border-[#a7f3d0] bg-white px-3 py-1.5 text-[13px] font-[500] text-[#047857] hover:bg-[#f0fdf4]"
                        >
                            Open
                        </a>
                        <button
                            type="button"
                            @click="copyLink"
                            class="rounded-[8px] bg-[#047857] px-3 py-1.5 text-[13px] font-[600] text-white hover:bg-[#065f46]"
                        >
                            Copy link
                        </button>
                    </div>
                </div>
            </div>

            <p v-else-if="isPublic" class="mt-5 text-[13px] text-[#898989]">
                Save to generate the public link.
            </p>

            <div class="mt-6 flex justify-end gap-3 border-t border-[#e5e7eb] pt-5">
                <button
                    type="button"
                    @click="close"
                    class="rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[13px] font-[500] text-[#374151] hover:bg-[#f8f9fa]"
                >
                    Cancel
                </button>
                <button
                    type="button"
                    :disabled="form.processing"
                    @click="save"
                    class="rounded-[8px] bg-[#111111] px-4 py-2 text-[13px] font-[600] text-white hover:bg-[#242424] disabled:opacity-50"
                >
                    Save sharing
                </button>
            </div>
        </div>
    </Modal>
</template>
