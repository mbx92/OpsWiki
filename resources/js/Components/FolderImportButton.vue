<script setup>
import { ref } from 'vue';

defineProps({
    label: {
        type: String,
        default: 'Pilih folder',
    },
});

const emit = defineEmits(['select']);

const input = ref(null);

const open = () => input.value?.click();

const onChange = (event) => {
    emit('select', Array.from(event.target.files ?? []));
    event.target.value = '';
};
</script>

<template>
    <div>
        <button
            type="button"
            class="inline-flex items-center rounded-[8px] border border-[#e5e7eb] bg-white px-3 py-2 text-[13px] font-[500] text-[#374151] transition-colors hover:border-[#111111] hover:text-[#111111]"
            @click="open"
        >
            {{ label }}
        </button>
        <input
            ref="input"
            type="file"
            webkitdirectory
            directory
            multiple
            class="hidden"
            @change="onChange"
        />
    </div>
</template>
