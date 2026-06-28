<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import Dropzone from 'dropzone';
import '../../css/dropzone-overrides.css';

Dropzone.autoDiscover = false;

const props = defineProps({
    modelValue: {
        type: [Array, Object, null],
        default: null,
    },
    multiple: {
        type: Boolean,
        default: false,
    },
    accept: {
        type: String,
        default: null,
    },
    maxFiles: {
        type: Number,
        default: null,
    },
    maxFilesize: {
        type: Number,
        default: 20,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    hint: {
        type: String,
        default: 'Drag & drop files here',
    },
    browseLabel: {
        type: String,
        default: 'click to browse',
    },
    error: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['update:modelValue']);

const dropzoneEl = ref(null);
const messageEl = ref(null);
let dropzone = null;

const syncModel = () => {
    if (!dropzone) {
        return;
    }

    const files = dropzone.getAcceptedFiles();

    emit('update:modelValue', props.multiple ? files : (files[0] ?? null));
};

const scheduleSync = () => {
    nextTick(syncModel);
};

const clear = () => {
    dropzone?.removeAllFiles(true);
    syncModel();
};

const isEmpty = (value) => {
    if (Array.isArray(value)) {
        return value.length === 0;
    }

    return !value;
};

onMounted(() => {
    dropzone = new Dropzone(dropzoneEl.value, {
        url: '#',
        autoProcessQueue: false,
        autoQueue: false,
        uploadMultiple: props.multiple,
        parallelUploads: props.multiple ? 10 : 1,
        maxFiles: props.maxFiles ?? (props.multiple ? null : 1),
        acceptedFiles: props.accept ?? undefined,
        maxFilesize: props.maxFilesize,
        createImageThumbnails: false,
        addRemoveLinks: false,
        clickable: messageEl.value,
        dictRemoveFile: 'Hapus',
        dictFileTooBig: 'File terlalu besar ({{filesize}} MB). Maks {{maxFilesize}} MB.',
        dictInvalidFileType: 'Tipe file tidak diizinkan.',
        dictMaxFilesExceeded: 'Hanya satu file yang diizinkan.',
        previewTemplate: `
            <div class="dz-preview dz-file-preview">
                <div class="dz-file-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125V5.625a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div class="dz-details">
                    <span class="dz-filename" data-dz-name></span>
                    <span class="dz-size" data-dz-size></span>
                    <div class="dz-error-message" data-dz-errormessage></div>
                </div>
                <button type="button" class="dz-remove" data-dz-remove>Hapus</button>
            </div>
        `.trim(),
        accept: (file, done) => {
            done();
            scheduleSync();
        },
    });

    dropzone.on('removedfile', scheduleSync);
    dropzone.on('error', scheduleSync);

    dropzone.on('maxfilesexceeded', (file) => {
        if (props.multiple) {
            dropzone.removeFile(file);

            return;
        }

        dropzone.removeAllFiles(true);
        dropzone.addFile(file);
    });

    if (props.disabled) {
        dropzone.disable();
    }
});

onBeforeUnmount(() => {
    dropzone?.destroy();
    dropzone = null;
});

watch(() => props.disabled, (disabled) => {
    if (!dropzone) {
        return;
    }

    if (disabled) {
        dropzone.disable();
    } else {
        dropzone.enable();
    }
});

watch(() => props.modelValue, (value) => {
    if (!dropzone || !isEmpty(value)) {
        return;
    }

    clear();
});

defineExpose({ clear });
</script>

<template>
    <div>
        <div ref="dropzoneEl" class="file-dropzone dropzone">
            <div ref="messageEl" class="dz-message">
                <div class="dz-upload-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </div>
                <p class="dz-title">{{ hint }}</p>
                <p class="dz-subtitle">
                    atau <span class="dz-browse">{{ browseLabel }}</span>
                </p>
            </div>
        </div>
        <p v-if="error" class="mt-1.5 text-[13px] text-[#ef4444]">{{ error }}</p>
    </div>
</template>
