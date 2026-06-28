<script setup>
import { computed } from 'vue';

const props = defineProps({
    linkable: { type: Array, default: () => [] },
    modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const selectedKeys = computed(() =>
    new Set(props.modelValue.map((item) => `${item.type}:${item.id}`)),
);

const isSelected = (type, id) => selectedKeys.value.has(`${type}:${id}`);

const toggle = (type, id) => {
    const key = `${type}:${id}`;
    const next = isSelected(type, id)
        ? props.modelValue.filter((item) => `${item.type}:${item.id}` !== key)
        : [...props.modelValue, { type, id }];
    emit('update:modelValue', next);
};
</script>

<template>
    <div class="space-y-4">
        <div
            v-for="group in linkable"
            :key="group.type"
            class="rounded-[8px] border border-[#e5e7eb] bg-[#fafafa] p-3"
        >
            <p class="mb-2 text-[12px] font-medium uppercase tracking-wide text-[#6b7280]">
                {{ group.label }}
            </p>
            <div v-if="group.items.length" class="max-h-40 space-y-1 overflow-y-auto">
                <label
                    v-for="item in group.items"
                    :key="`${group.type}-${item.id}`"
                    class="flex cursor-pointer items-center gap-2 rounded px-1 py-1 text-[13px] hover:bg-white"
                >
                    <input
                        type="checkbox"
                        class="rounded border-[#d1d5db]"
                        :checked="isSelected(group.type, item.id)"
                        @change="toggle(group.type, item.id)"
                    />
                    <span class="truncate text-[#374151]">{{ item.title }}</span>
                </label>
            </div>
            <p v-else class="text-[12px] text-[#9ca3af]">No items</p>
        </div>
        <p v-if="!linkable.length" class="text-[13px] text-[#9ca3af]">No linkable items yet.</p>
    </div>
</template>
