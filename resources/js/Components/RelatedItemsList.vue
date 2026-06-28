<script setup>
defineProps({ items: { type: Array, default: () => [] } });
</script>

<template>
    <section v-if="items.length" class="rounded-[12px] border border-[#e5e7eb] bg-white p-6">
        <h2 class="mb-3 text-[14px] font-[600] uppercase tracking-wider text-[#898989]">Related</h2>
        <div class="space-y-2">
            <a
                v-for="item in items"
                :key="`${item.type}-${item.id}`"
                :href="item.url"
                class="flex items-center justify-between rounded-[8px] border border-[#e5e7eb] px-4 py-2 text-[14px] hover:bg-[#f8f9fa]"
            >
                <span class="font-[500] text-[#111111]">{{ item.title }}</span>
                <span class="text-[12px] capitalize text-[#898989]">{{ item.type.replace('_', ' ') }}</span>
            </a>
        </div>
    </section>
</template>
