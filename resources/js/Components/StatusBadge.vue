<script setup>
defineProps({
    status: { type: String, required: true },
});

const colors = {
    draft: 'bg-[#f5f5f5] text-[#6b7280]',
    review: 'bg-[#fef3c7] text-[#92400e]',
    tested: 'bg-[#dbeafe] text-[#1e40af]',
    production: 'bg-[#d1fae5] text-[#065f46]',
    deprecated: 'bg-[#fee2e2] text-[#991b1b]',
    archived: 'bg-[#f3f4f6] text-[#898989]',
    new: 'bg-[#dbeafe] text-[#1e40af]',
    converted: 'bg-[#d1fae5] text-[#065f46]',
    open: 'bg-[#fee2e2] text-[#991b1b]',
    active: 'bg-[#d1fae5] text-[#065f46]',
};
</script>

<template>
    <span
        class="inline-flex items-center rounded-[9999px] px-2.5 py-0.5 text-[12px] font-[500] capitalize"
        :class="colors[status] || 'bg-[#f5f5f5] text-[#6b7280]'"
    >
        {{ status.replace('_', ' ') }}
    </span>
</template>
