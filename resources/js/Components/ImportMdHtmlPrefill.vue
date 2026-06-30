<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import axios from 'axios';
import { ref } from 'vue';
import ImportMarkdownGuide from '@/Components/ImportMarkdownGuide.vue';

const props = defineProps({
    type: {
        type: String,
        required: true,
    },
    accept: {
        type: String,
        default: '.md,.markdown,.html,.htm',
    },
    hint: {
        type: String,
        default: 'Upload file .md atau .html untuk mengisi form otomatis.',
    },
});

const emit = defineEmits(['parsed']);

const fileInput = ref(null);
const loading = ref(false);
const error = ref(null);
const lastFile = ref(null);

const openPicker = () => {
    fileInput.value?.click();
};

const onFileChange = async (event) => {
    const file = event.target.files?.[0];

    if (!file) {
        return;
    }

    loading.value = true;
    error.value = null;
    lastFile.value = file.name;

    const formData = new FormData();
    formData.append('file', file);

    try {
        const { data } = await axios.post(route('import.parse', props.type), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        emit('parsed', data);
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Gagal membaca file.';
    } finally {
        loading.value = false;
        if (fileInput.value) {
            fileInput.value.value = '';
        }
    }
};
</script>

<template>
    <div class="rounded-[8px] border border-dashed border-[#e5e7eb] bg-[#f8f9fa] p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <InputLabel value="Import dari file" />
                <p class="mt-0.5 text-[12px] text-[#898989]">{{ hint }}</p>
            </div>
            <button
                type="button"
                class="rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-1.5 text-[13px] font-[500] text-[#374151] transition-colors hover:bg-[#f8f9fa] disabled:opacity-50"
                :disabled="loading"
                @click="openPicker"
            >
                {{ loading ? 'Membaca…' : 'Pilih file' }}
            </button>
        </div>
        <input
            ref="fileInput"
            type="file"
            class="hidden"
            :accept="accept"
            @change="onFileChange"
        />
        <p v-if="lastFile && !error && !loading" class="mt-2 text-[12px] text-[#22c55e]">
            ✓ {{ lastFile }} — form diperbarui
        </p>
        <p v-if="error" class="mt-2 text-[12px] text-[#ef4444]">{{ error }}</p>

        <ImportMarkdownGuide
            v-if="['sop', 'troubleshooting'].includes(type)"
            :type="type"
            compact
            class="mt-3"
        />
    </div>
</template>
