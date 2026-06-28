<script setup>
import InputLabel from '@/Components/InputLabel.vue';

const props = defineProps({
    linkablePages: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const pageType = 'pages';

const selectedIds = () => props.modelValue.filter(r => r.type === pageType).map(r => r.id);

const togglePage = (event) => {
    const id = Number(event.target.value);
    const checked = event.target.checked;
    let next = [...props.modelValue];

    if (checked) {
        next.push({ type: pageType, id });
    } else {
        next = next.filter(r => !(r.type === pageType && r.id === id));
    }

    emit('update:modelValue', next);
};
</script>

<template>
    <div>
        <InputLabel value="Related pages" />
        <p class="mt-1 text-[13px] text-[#898989]">Link to other wiki pages for cross-reference.</p>
        <div class="mt-3 max-h-48 space-y-1 overflow-y-auto rounded-[8px] border border-[#e5e7eb] p-3">
            <label
                v-for="page in linkablePages"
                :key="page.id"
                class="flex items-center gap-2 rounded-[6px] px-2 py-1.5 text-[14px] hover:bg-[#f8f9fa]"
            >
                <input
                    type="checkbox"
                    :value="page.id"
                    :checked="selectedIds().includes(page.id)"
                    @change="togglePage"
                />
                <span>{{ page.title }}</span>
            </label>
            <p v-if="!linkablePages.length" class="text-[13px] text-[#898989]">No other pages available.</p>
        </div>
    </div>
</template>
